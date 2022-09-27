<?php

use OmnivaApi\Sender;
use OmnivaApi\Receiver;
use OmnivaApi\Parcel;
use OmnivaApi\Order;
use OmnivaApi\Item;

class OmnivaIntEntityBuilder
{
    const MAX_DESCRIPTION_LENGTH = 39;

    private $api;

    public function __construct($api)
    {
        $this->api = $api;
    }

    public function buildSender()
    {
        $sender = new Sender();
        $sender
            ->setShippingType('courier')
            ->setCompanyName(Configuration::get('OMNIVA_SENDER_NAME'))
            ->setContactName(Configuration::get('OMNIVA_SHOP_CONTACT'))
            ->setStreetName(Configuration::get('OMNIVA_SHOP_ADDRESS'))
            ->setZipcode(Configuration::get('OMNIVA_SHOP_POSTCODE'))
            ->setCity(Configuration::get('OMNIVA_SHOP_CITY'))
            ->setPhoneNumber(Configuration::get('OMNIVA_SHOP_PHONE'))
            ->setCountryId(Configuration::get('OMNIVA_SHOP_COUNTRY_CODE'));
        return $sender;
    }

    private function addStateCode($receiver, $address)
    {
        $states = (array) $this->api->listAllStates();
        $countryIso = Country::getIsoById($address->id_country);
        $id_state = $address->id_state;
        $state = new State($id_state);
        $state_iso = $state->iso_code;

        foreach($states as $state)
        {
            if($state->country_code == $countryIso && $state->code == $state_iso)
            {
                $receiver->setStateCode($state_iso);
                return;
            }
        }
    }

    public function buildReceiver($cart, $type)
    {
        $address = new Address($cart->id_address_delivery);
        $country_code = OmnivaIntCountry::getCountryIdByIso(Country::getIsoById($address->id_country));
        $receiver = new Receiver($type);

        $receiver
        ->setShippingType($type)
        ->setContactName($address->firstname . ' ' . $address->lastname)
        ->setPhoneNumber($address->phone)
        ->setCountryId($country_code);

        if($address->id_state)
            $this->addStateCode($receiver, $address);

        $cartTerminal = new OmnivaIntCartTerminal($cart->id);
        if($type == 'courier' || ($type == 'terminal' && !Validate::isLoadedObject($cartTerminal)))
        {
            $receiver
            ->setStreetName($address->address1)
            ->setZipcode($address->postcode)
            ->setCity($address->city);

        }
        elseif($type == 'terminal')
        {
            $terminal = new OmnivaIntTerminal($cartTerminal->id_terminal);
            $receiver
            ->setStreetName($terminal->address)
            ->setZipcode($terminal->id)
            ->setCity($address->city);
        }

        $customer = new Customer($address->id_customer);
        if($customer->company && $address->company)
        {
            $receiver->setCompanyName($address->company);
        }

        return $receiver;
    }

    public function buildParcels($cart)
    {
        $cart_products = $cart->getProducts();
        $parcels = [];
        foreach ($cart_products as $product)
        {
            $id_category = $product['id_category_default'];
            $parcel = new Parcel();
            $amount = (int) $product['cart_quantity'];
            $parcel->setAmount($amount);
            $omnivaCategory = new OmnivaIntCategory($id_category);
            
            if($omnivaCategory->active)
            {
                $parcel
                ->setUnitWeight($this->unZero($omnivaCategory->weight) * $amount)
                ->setWidth($this->unZero($omnivaCategory->width) * $amount)
                ->setLength($this->unZero($omnivaCategory->length) * $amount)
                ->setHeight($this->unZero($omnivaCategory->height) * $amount);
            }
            else
            {
                $parcel
                ->setUnitWeight($this->unZero($product['weight']) * $amount)
                ->setWidth($this->unZero($product['width']) * $amount)
                ->setLength($this->unZero($product['depth']) * $amount)
                ->setHeight($this->unZero($product['height']) * $amount);
            }
            $parcels[] = $parcel->generateParcel(); 
        }
        return $parcels;
    }


    public function buildItems($order)
    {
        $order_products = $order->getProducts();
        $items = [];

        $address = new Address($order->id_address_delivery);
        $country_code = OmnivaIntCountry::getCountryIdByIso(Country::getIsoById($address->id_country));
        foreach ($order_products as $product)
        {
            $amount = (int) $product['cart_quantity'];

            $item = new Item();
            $item
                ->setDescription(substr($product['name'], 0, self::MAX_DESCRIPTION_LENGTH))
                ->setItemPrice($product['price'])
                ->setItemAmount($amount)
                ->setCountryId($country_code);
            $items[] = $item->generateItem();
        }
        return $items;
    }

    public function buildOrder($order)
    {
        
        $carrier = new Carrier($order->id_carrier);
        if(!Validate::isLoadedObject($carrier))
            return false;
        $omnivaCarrier = OmnivaIntCarrier::getCarrierByReference($carrier->id_reference);
        if(!Validate::isLoadedObject($omnivaCarrier))
            return false;

        $type = $omnivaCarrier->type;

        $cart = new Cart($order->id_cart);
        $receiver = $this->buildReceiver($cart, $type);
        $sender = $this->buildSender();

        $parcels = $this->buildParcels($cart);
        $items = $this->buildItems($cart);
        
        $omnivaOrder = new OmnivaIntOrder($order->id);

        $additional_services = [
            'cod' => $omnivaOrder->cod,
            'insurance' => $omnivaOrder->insurance,
            'carry_service' => $omnivaOrder->carry_service,
            'doc_return' => $omnivaOrder->doc_return,
            'fragile' => $omnivaOrder->fragile,
        ];

        $cod_amount = 0;
        if($omnivaOrder->cod)
            $cod_amount = $omnivaOrder->cod_amount;
        

        $reference = $order->reference;
        $order = new Order();
        
        $order
            ->setServiceCode($omnivaOrder->service_code)
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setParcels($parcels)
            ->setReference($reference)
            ->setItems($items)
            ->setAdditionalServices($additional_services, $cod_amount);
        return $order;
    }

    // Hacky way to avoid API exception as it does not allow any zero values (weight, length, width, height) for items
    public function unZero($value)
    {
        return $value == 0 ? 1 : $value;
    }
}