<?php

use OmnivaApi\API;
use OmnivaApi\Sender;
use OmnivaApi\Receiver;
use OmnivaApi\Parcel;

require_once 'OmnivaIntEntityBuilder.php';

class OmnivaIntOffersProvider
{
    private $cart;

    private $offers;

    private $carrier;

    private $module;

    private $entityBuilder;

    private $type;

    public function __construct()
    {
        $this->entityBuilder = new OmnivaIntEntityBuilder();
    }

    private function cartIsSuitableForCarriers($cart)
    {
        $cart_products = $cart->getProducts();
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

    private function checkIfCarrierMatchesOffers($omnivaCarrier, $offers)
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

    private function addSurcharge($price, $omnivaCarrierCountry)
    {
        $omniva_price = $omnivaCarrierCountry->price;
        $price_type = $omnivaCarrierCountry->price_type;
        if($price_type == 'surcharge-percent')
        {
            $price *= (1 + $omniva_price / 100);
        }
        elseif($price_type == 'surcharge-fixed')
        {
            $price *= + $omniva_price;
        }
        else
            return false;

        $taxRate = $omnivaCarrierCountry->tax;
        return $this->addTax($price, $taxRate);
    }

    private function addTax($price, $taxRate = 0)
    {
        $use_tax = (int) Configuration::get('PS_TAX');
        if(!$taxRate || !$use_tax)
            return $price;
        else
            return ($price * (1 + ($taxRate / 100.0)));
    }

    public function getPrice()
    {
        $context = Context::getContext();
        $cookie = $context->cookie;
        $cart = $this->cart;
        $omnivaCarrier = $this->carrier;
        $cart_without_shipping = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);

        $address = new Address($cart->id_address_delivery);

        // If there is no address in the cart, it's not possible to fetch rates yet.
        if(!Validate::isLoadedObject($address))
            return false;

        // Check if destination country is disabled for the carrier.
        $omnivaCarrierCountry = OmnivaIntCarrierCountry::getCarrierCountry($omnivaCarrier->id, $address->id_country);
        if(!Validate::isLoadedObject($omnivaCarrierCountry) || !$omnivaCarrierCountry->active)
            return false;

        $sender = $this->entityBuilder->buildSender($this->type);
        $receiver = $this->entityBuilder->buildReceiver($cart, $this->type);
        $parcels = $this->entityBuilder->buildParcels($cart);

        $offers = $this->module->helper->getApi()->getOffers($sender, $receiver, $parcels);
        
        // Check if this carrier matches any offer. Return false, if no.
        if(!empty($offers) && $carrier_offers = $this->checkIfCarrierMatchesOffers($omnivaCarrier, $offers))
        {
            // If price type is fixed, we just return that fixed price for all applicable services.
            if($omnivaCarrierCountry->price_type == 'fixed')
            {
                $cookie->{'omniva_carrier_' . $omnivaCarrier->id_reference} = $carrier_offers[0]->service_code;
                $cookie->write();
                if($cart_without_shipping >= $omnivaCarrierCountry->free_shipping)
                {
                    return 0;
                }
                return $this->addTax($omnivaCarrierCountry->price, $omnivaCarrierCountry->tax);
            }


            // When there is only one offer, simply return it's price.
            if(count($carrier_offers) == 1)
            {
                $offer = $carrier_offers[0];
                $cookie->{'omniva_carrier_' . $omnivaCarrier->id_reference} = $offer->service_code;
                $cookie->write();
                if($cart_without_shipping >= $omnivaCarrierCountry->free_shipping)
                {
                    return 0;
                }
                return $this->addSurcharge($offer->total_price_excl_vat, $omnivaCarrierCountry);
            }
            else
            {
                if($cart_without_shipping >= $omnivaCarrierCountry->free_shipping)
                {
                    return 0;
                }
                if($omnivaCarrierCountry->cheapest)
                {
                    $prices = array_map(function($offer) {
                        return (float) $offer->total_price_excl_vat;
                    }, $carrier_offers);
                    asort($prices);
                    $cheapest_key = key($prices);
                    $cookie->{'omniva_carrier_' . $omnivaCarrier->id_reference} = $carrier_offers[$cheapest_key]->service_code;
                    $cookie->write();
                    return $this->addSurcharge(reset($prices), $omnivaCarrierCountry);
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
                    $fastest_key = key($lower_bound_days);
                    $cookie->{'omniva_carrier_' . $omnivaCarrier->id_reference} = $carrier_offers[$fastest_key]->service_code;
                    $cookie->write();
                    return $this->addSurcharge($carrier_offers[$fastest_key]->price, $omnivaCarrierCountry);
                }
            }
        }
        else
            return false;
    }

    public function filterOffersByCategories($offers)
    {
        return $offers;
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

    /**
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */ 
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}