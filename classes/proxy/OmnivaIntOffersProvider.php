<?php

use OmnivaApi\API;
use OmnivaApi\Sender;
use OmnivaApi\Receiver;
use OmnivaApi\Parcel;

require_once __DIR__ . "/../models/OmnivaIntCountry.php";

class OmnivaIntOffersProvider
{
    private $cart;

    private $offers;

    private $carrier;

    private $module;
    
    public function getSender($type)
    {
        $sender = new Sender();
        $sender
            ->setShippingType($type)
            ->setCompanyName(Configuration::get($this->module->getConfigKey('sender_name', 'SHOP')))
            ->setContactName(Configuration::get($this->module->getConfigKey('shop_contact', 'SHOP')))
            ->setStreetName(Configuration::get($this->module->getConfigKey('shop_address', 'SHOP')))
            ->setZipcode(Configuration::get($this->module->getConfigKey('shop_postcode', 'SHOP')))
            ->setCity(Configuration::get($this->module->getConfigKey('shop_city', 'SHOP')))
            ->setPhoneNumber(Configuration::get($this->module->getConfigKey('shop_phone', 'SHOP')))
            ->setCountryId(Configuration::get($this->module->getConfigKey('shop_country_code', 'SHOP')));
        return $sender;
    }

    public function getReceiver($address)
    {
        $country = new Country();
        $country_code = OmnivaIntCountry::getCountryIdByIso($country->getIsoById($address->id_country));

        $receiver = new Receiver('courier');
        $receiver
            ->setShippingType('courier')
            ->setContactName($address->firstname . ' ' . $address->lastname)
            ->setStreetName($address->address1)
            ->setZipcode($address->postcode)
            ->setCity($address->city)
            ->setPhoneNumber($address->phone)
            ->setCountryId($country_code);

        $customer = new Customer($address->id_customer);
        if($customer->company && $address->company)
        {
            $receiver->setCompanyName($address->company);
        }

        return $receiver;
    }

    public function getParcels($cart)
    {
        $cart_products = $cart->getProducts();
        $parcels = [];
        foreach ($cart_products as $product)
        {
            $id_category = $product['id_category_default'];
            $omnivaCategory = new OmnivaIntCategory($id_category);
            
            $parcel = new Parcel();
            $parcel
                ->setAmount((int) $product['cart_quantity'])
                ->setUnitWeight($this->unZero($omnivaCategory->weight))
                ->setWidth($this->unZero($omnivaCategory->width))
                ->setLength($this->unZero($omnivaCategory->length))
                ->setHeight($this->unZero($omnivaCategory->height));
            $parcels[] = $parcel->generateParcel(); 
        }
        return $parcels;
    }

    public function cartIsSuitableForCarriers($cart)
    {
        $cart_products = $cart->getProducts();
        $parcels = [];
        foreach ($cart_products as $product)
        {
            $id_category = $product['id_category_default'];
            $omnivaCategory = new OmnivaIntCategory($id_category);            
            if(!Validate::isLoadedObject($omnivaCategory) || !$omnivaCategory->active)
            {
                return false;
            }
        }
        return true;
    }

    public function checkIfCarrierMatchesOffers($omnivaCarrier, $offers)
    {
        // First find appropriate offers.
        $codes = OmnivaIntCarrierService::getCarrierServicesCodes($omnivaCarrier->id);
        $carrier_offers = [];
        foreach($offers as $offer)
        {
            if(in_array($offer->service_code, $codes))
            {
                $carrier_offers[] = $offer;
            }
        }
        return !empty($carrier_offers) ? $carrier_offers : false;
    }

    public function addSurcharge($price, $omniva_price, $price_type)
    {
        if($price_type == 'surcharge-percent')
        {
            return $price * (1 + $omniva_price / 100);
        }
        elseif($price_type == 'surcharge-fixed')
        {
            return $price + $omniva_price;
        }
        else
            return false;
    }

    public function getPrice()
    {
        $cart = $this->cart;
        if(!$this->cartIsSuitableForCarriers($cart))
        {
            return false;
        }
        
        $omnivaCarrier = $this->carrier;
        $cart_without_shipping = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);

        $address = new Address($cart->id_address_delivery);
        $sender = $this->getSender('courier');
        $receiver = $this->getReceiver($address);
        $parcels = $this->getParcels($cart);

        $offers = $this->module->api->getOffers($sender, $receiver, $parcels);
        
        // Check if this carrier matches any offer. Return false, if no.
        if(!empty($offers) && $carrier_offers = $this->checkIfCarrierMatchesOffers($omnivaCarrier, $offers))
        {
            // If price type is fixed, we just return that fixed price for all applicable services.
            if($omnivaCarrier->price_type == 'fixed')
            {
                if($cart_without_shipping >= $omnivaCarrier->free_shipping)
                {
                    return 0;
                }
                return $omnivaCarrier->price; 
            }


            // When there is only one offer, simply return it's price.
            if(count($carrier_offers) == 1)
            {
                $offer = $carrier_offers[0];
                if($cart_without_shipping >= $omnivaCarrier->free_shipping)
                {
                    return 0;
                }
                return $this->addSurcharge($offer->price, $omnivaCarrier->price, $omnivaCarrier->price_type);
            }
            else
            {
                if($omnivaCarrier->cheapest)
                {
                    $prices = array_map(function($offer) {
                        return (float) $offer->price;
                    }, $carrier_offers);
                    sort($prices);
                    return $this->addSurcharge($prices[0], $omnivaCarrier->price, $omnivaCarrier->price_type);
                }
                // Fastest
                else
                {
                    $lower_bound_days = array_map(function($offer) {
                        return (int) explode('-', $offer->delivery_time)[0];
                    }, $carrier_offers);
                    // Check the lower bound day (format: X-Y days)
                    asort($lower_bound_days);

                    // Get first key, which will correspond to fastest offer (parallel arrays)
                    $fastest_key = array_key_first($lower_bound_days);
                    return $this->addSurcharge($carrier_offers[$fastest_key]->price, $omnivaCarrier->price, $omnivaCarrier->price_type);
                }
            }
        }
        else
            return false;
    }

    // Hacky way to avoid API exception as it does not allow any zero values (weight, length, width, height) for items
    public function unZero($value)
    {
        return $value == 0 ? 0.001 : $value;
    }

    /**
     * Set the value of cart
     *
     * @return  self
     */ 
    public function setCart($cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Set the value of offers
     *
     * @return  self
     */ 
    public function setOffers($offers)
    {
        $this->offers = $offers;

        return $this;
    }

    /**
     * Set the value of carrier
     *
     * @return  self
     */ 
    public function setCarrier($carrier)
    {
        $this->carrier = $carrier;

        return $this;
    }

    /**
     * Set the value of module
     *
     * @return  self
     */ 
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }
}