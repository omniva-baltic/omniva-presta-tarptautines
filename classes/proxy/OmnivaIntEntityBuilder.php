<?php

use OmnivaApi\Sender;
use OmnivaApi\Receiver;
use OmnivaApi\Parcel;
use OmnivaApi\Order;
use OmnivaApi\Item;

class OmnivaIntEntityBuilder
{
    const MAX_DESCRIPTION_LENGTH = 39;

    private $module;

    public function __construct($module)
    {
        $this->module = $module;
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
        $states = (array) $this->module->helper->getApi()->listAllStates();
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

        $consolidation = $this->module->helper->getConfigValue('consolidation');

        if($consolidation)
        {
            $parcel = new Parcel();
            $parcel->setAmount(1);

            $totalWidth = $totalLength = $totalHeight = $totalWeight = 0;
            foreach ($cart_products as $product)
            {
                $id_category = $product['id_category_default'];
                $amount = (int) $product['cart_quantity'];
                $omnivaCategory = new OmnivaIntCategory($id_category);
                
                if($product['weight'] != 0 && $product['width'] != 0 && $product['depth'] != 0 && $product['height'] != 0)
                {
                    $totalWeight +=  $this->unZero($product['weight']) * $amount;
                    $totalWidth += $this->unZero($product['width']) * $amount;
                    $totalLength += $this->unZero($product['depth']) * $amount;
                    $totalHeight += $this->unZero($product['height']) * $amount;
                }
                elseif($omnivaCategory->active)
                {
                    $totalWeight +=  $this->unZero($omnivaCategory->weight) * $amount;
                    $totalWidth += $this->unZero($omnivaCategory->width) * $amount;
                    $totalLength += $this->unZero($omnivaCategory->length) * $amount;
                    $totalHeight += $this->unZero($omnivaCategory->height) * $amount;
                }
                else
                {
                    $totalWeight +=  $this->unZero(1) * $amount;
                    $totalWidth += $this->unZero(1) * $amount;
                    $totalLength += $this->unZero(1) * $amount;
                    $totalHeight += $this->unZero(1) * $amount;
                }
            }
            $parcel->setUnitWeight($this->unZero($totalWeight));
            $volume = $totalWidth * $totalLength * $totalHeight;

            $averageDimension = ceil($volume ** (1/3));
            $parcel
                ->setWidth($averageDimension)
                ->setLength($averageDimension)
                ->setHeight($averageDimension);

            $parcels[] = $parcel->generateParcel(); 
        }
        else
        {
            foreach ($cart_products as $product)
            {
                $id_category = $product['id_category_default'];
                $parcel = new Parcel();
                $amount = (int) $product['cart_quantity'];
                $parcel->setAmount($amount);
                $omnivaCategory = new OmnivaIntCategory($id_category);
                
                if($product['weight'] != 0 && $product['width'] != 0 && $product['depth'] != 0 && $product['height'] != 0)
                {
                    $parcel
                        ->setUnitWeight($product['weight'] * $amount)
                        ->setWidth($product['width'] * $amount)
                        ->setLength($product['depth'] * $amount)
                        ->setHeight($product['height'] * $amount);
                }
                elseif($omnivaCategory->active)
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
                    ->setUnitWeight($this->unZero(1) * $amount)
                    ->setWidth($this->unZero(1) * $amount)
                    ->setLength($this->unZero(1) * $amount)
                    ->setHeight($this->unZero(1) * $amount);
                }
                $parcels[] = $parcel->generateParcel(); 
            }
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

        $additional_services = [];
        if($omnivaOrder->cod)
            $additional_services[] = 'cod';
        if($omnivaOrder->insurance)
            $additional_services[] = 'insurance';
        if($omnivaOrder->carry_service)
            $additional_services[] = 'carry_service';        
        if($omnivaOrder->doc_return)
            $additional_services[] = 'doc_return';        
        if($omnivaOrder->fragile)
            $additional_services[] = 'fragile';

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