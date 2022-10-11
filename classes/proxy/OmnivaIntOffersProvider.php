<?php

use OmnivaApi\API;
use OmnivaApi\Sender;
use OmnivaApi\Receiver;
use OmnivaApi\Parcel;

require_once 'OmnivaIntEntityBuilder.php';

class OmnivaIntOffersProvider
{
    const MAX_WEIGHT_KG = 2.5;

    const MAX_SUM_EDGES_CM = 90;

    const MAX_EDGE_CM = 60;

    private $cart;

    private $offers;

    private $carrier;

    private $module;

    private $entityBuilder;

    private $type;

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
            $price += $omniva_price;
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

    private function getOfferPrice($offer)
    {
        if(!is_object($offer))
            return 0;
        $offers_tax = $this->module->helper->getConfigValue('offers_tax');

        if($offers_tax && !empty($offer->total_price_with_vat))
        {
            return (float) $offer->total_price_with_vat;
        }
        if(!$offers_tax && !empty($offer->total_price_excl_vat))
        {
            return (float) $offer->total_price_excl_vat;
        }

        //Default to price 
        if(!empty($offer->price))
        {
            return (float) $offer->price;
        }

        return 0;
    }

    public function getPrice()
    {
        $this->entityBuilder = new OmnivaIntEntityBuilder($this->module);

        $context = Context::getContext();
        $cookie = $context->cookie;
        $cart = $this->cart;
        $omnivaCarrier = $this->carrier;
        $cart_without_shipping = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);

        $address = new Address($cart->id_address_delivery);

        // If there is no address in the cart and carrier uses fixed rates - give them.
        // Otherwise, fetching rates is not possible.
        if(!Validate::isLoadedObject($address))
        {
            if($omnivaCarrier->price_type == 'fixed')
            {
                if($cart_without_shipping >= $omnivaCarrier->free_shipping)
                {
                    return 0;
                }
                return $this->addTax($omnivaCarrier->price, $omnivaCarrier->tax);
            }
            return false;
        }

        // Check if destination country is disabled for the carrier.
        $omnivaCarrierCountry = OmnivaIntCarrierCountry::getCarrierCountry($omnivaCarrier->id, $address->id_country);
        if(!Validate::isLoadedObject($omnivaCarrierCountry) || !$omnivaCarrierCountry->active)
            return false;

        $sender = $this->entityBuilder->buildSender();
        $receiver = $this->entityBuilder->buildReceiver($cart, $this->type);
        $parcels = $this->entityBuilder->buildParcelsCart($cart);

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
                return $this->addSurcharge($this->getOfferPrice($offer), $omnivaCarrierCountry);
            }
            else
            {
                if($cart_without_shipping >= $omnivaCarrierCountry->free_shipping)
                {
                    return 0;
                }
                if($omnivaCarrierCountry->cheapest)
                {
                    $prices = array_map([$this, 'getOfferPrice'], $carrier_offers);
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
                    return $this->addSurcharge($this->getOfferPrice($carrier_offers[$fastest_key]), $omnivaCarrierCountry);
                }
            }
        }
        elseif($omnivaCarrierCountry->is_exception)
        {
            // Determine by how much the exception price will multiplied (depends on cart)
            $multiplier = $this->calculateExceptionPriceMultiplier($cart);
            if($multiplier)
            {
                $cookie->{'omniva_carrier_exception_' . $omnivaCarrier->id_reference} = 1;
                $cookie->write();
                return $multiplier * $omnivaCarrierCountry->exception_price;
            }
            return false;
        }
        else
            return false;
    }

    private function calculateExceptionPriceMultiplier($cart)
    {
        $cart_products = $cart->getProducts();

        // Check for exception in separate loop, before doing actual calculations.
        foreach ($cart_products as $product)
        {
            $tooHeavy = $product['weight'] > self::MAX_WEIGHT_KG;
            $someEdgeTooLarge = $product['width'] > self::MAX_EDGE_CM || $product['depth'] > self::MAX_EDGE_CM || $product['height'] > self::MAX_EDGE_CM;
            $tooLargeSumEdges = ($product['width'] + $product['depth'] + $product['height']) > self::MAX_SUM_EDGES_CM;
            if($tooHeavy || $someEdgeTooLarge || $tooLargeSumEdges)
                return false;
        }

        // If no limits were breached, then do the actual size calculation.
        $totalWeight = $totalVolume = 0;

        // Starting with one box and incrementing it, if needed.
        $numBoxes = 1;
        foreach ($cart_products as $product)
        {
            $amount = (int) $product['cart_quantity'];
            $weight = $product['weight'] ?: 1;
            $height = $product['height'] ?: 1;
            $depth = $product['depth'] ?: 1;
            $width = $product['width'] ?: 1;

            for($i = 0; $i < $amount; $i++)
            {
                $tooHeavy = ($totalWeight + $weight) >= self::MAX_WEIGHT_KG;

                $avgEdge = ($totalVolume + ($height * $depth * $width)) ** (1/3);
                $tooLargeSumEdges = ($avgEdge * 3) > self::MAX_SUM_EDGES_CM;

                if($tooHeavy || $tooLargeSumEdges)
                {
                    $numBoxes++;
                    $totalWeight = $totalVolume = 0;
                }
                $totalWeight += $weight;
                $totalVolume += ($height * $depth * $width);
            }
        }

        return $numBoxes;
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