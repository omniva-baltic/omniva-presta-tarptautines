<?php

use OmnivaApi\Sender;
use OmnivaApi\Receiver;
use OmnivaApi\Parcel;
use OmnivaApi\Order;
use OmnivaApi\Item;

require_once __DIR__ . "/../models/OmnivaIntCountry.php";
require_once __DIR__ . "/../models/OmnivaIntOrder.php";

class OmnivaIntEntityBuilder
{
    public function buildSender($type)
    {
        $sender = new Sender();
        $sender
            ->setShippingType($type)
            ->setCompanyName(Configuration::get('OMNIVA_SENDER_NAME'))
            ->setContactName(Configuration::get('OMNIVA_SHOP_CONTACT'))
            ->setStreetName(Configuration::get('OMNIVA_SHOP_ADDRESS'))
            ->setZipcode(Configuration::get('OMNIVA_SHOP_POSTCODE'))
            ->setCity(Configuration::get('OMNIVA_SHOP_CITY'))
            ->setPhoneNumber(Configuration::get('OMNIVA_SHOP_PHONE'))
            ->setCountryId(Configuration::get('OMNIVA_SHOP_COUNTRY_CODE'));
        return $sender;
    }

    public function buildReceiver($address)
    {
        $country_code = OmnivaIntCountry::getCountryIdByIso(Country::getIsoById($address->id_country));

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
                ->setDescription($product['name'])
                ->setItemPrice($product['price'])
                ->setItemAmount($amount)
                ->setCountryId($country_code);
            $items[] = $item->generateItem();
        }
        return $items;
    }

    public function buildOrder($order)
    {
        $sender = $this->buildSender('courier');
        $address = new Address($order->id_address_delivery);
        $cart = new Cart($order->id_cart);
        $receiver = $this->buildReceiver($address);
        $parcels = $this->buildParcels($cart);
        $items = $this->buildItems($cart);
        $omnivaOrder = new OmnivaIntOrder($order->id);
        $reference = $order->reference;
        $order = new Order();
        $order
            ->setServiceCode($omnivaOrder->service_code)
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setParcels($parcels)
            ->setReference($reference)
            ->setItems($items);
        return $order;
    }

    // Hacky way to avoid API exception as it does not allow any zero values (weight, length, width, height) for items
    public function unZero($value)
    {
        return $value == 0 ? 1 : $value;
    }
}