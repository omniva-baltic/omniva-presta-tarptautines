<?php

namespace OmnivaApi;

use OmnivaApi\Exception\OmnivaApiException;
use OmnivaApi\Sender;
use OmnivaApi\Receiver;
use OmnivaApi\Item;
use OmnivaApi\Parcel;

class Order
{
    private $service_code;
    private $sender;
    private $receiver;
    private $parcels = array();
    private $items = array();
    private $reference;
    private $callback_urls;
	private $additional_services = array();
	private $cod_amount = 0;


    public function __construct()
    {

    }

    public function addParcels($parcels)
    {
        if (is_object($parcels)) {
            array_push($this->parcels, $parcels->generateParcel());
            return $this;
        } else {
            array_merge($this->parcels, $parcels);
        }

        return $this;
    }

    public function addItems($items)
    {
        if (is_object($items)) {
            array_push($this->items, $items->generateItem());
        } else {
            array_merge($this->items, $items);
        }

        return $this;
    }

    public function setServiceCode($service_code)
    {
        $this->service_code = $service_code;

        return $this;
    }
	
	public function setAdditionalServices($services, $cod_amount = 0)
    {
        $this->additional_services = $services;
		$this->cod_amount = $cod_amount;

        return $this;
    }

    public function setSender(Sender $sender)
    {
        $this->sender = $sender;

        return $this;
    }

    public function setReceiver(Receiver $receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function setParcels($parcels)
    {
        $this->parcels = $parcels;

        return $this;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    public function setCallbackUrls($callback_urls)
    {
        $this->callback_urls = $callback_urls;

        return $this;
    }

    public function generateOrder()
    {
        if (!$this->service_code) throw new OmnivaApiException('All the fields must be filled. service_code is missing.');
        if (!$this->sender) throw new OmnivaApiException('All the fields must be filled. sender is missing.');
        if (!$this->receiver) throw new OmnivaApiException('All the fields must be filled. receiver is missing.');
        if (!$this->parcels) throw new OmnivaApiException('All the fields must be filled. parcels are missing.');
        if (!$this->items) throw new OmnivaApiException('All the fields must be filled. items are missing.');
        if (!$this->reference) throw new OmnivaApiException('All the fields must be filled. reference is missing.');
        if (in_array('cod', $this->additional_services) && !$this->cod_amount) throw new OmnivaApiException('Selected COD, but cod amount is 0');

        $order_data = array(
            'service_code' => $this->service_code,
            'sender' => $this->sender->generateSender(),
            'receiver' => $this->receiver->generateReceiver(),
            'parcels' => $this->parcels,
            'reference' => $this->reference,
            'export_items' => $this->items,
            'callback_urls' => $this->callback_urls
        );
		
		foreach ($this->additional_services as $service){
			$order_data[$service] = $service === 'cod' ? $this->cod_amount : 'true';
		}
		
		return $order_data;
    }

    public function returnJson()
    {
        return json_encode($this->generateOrder());
    }

    public function __toArray()
    {
        return $this->generateOrder();
    }
}
