<?php

namespace OmnivaApi;

use OmnivaApi\Exception\OmnivaApiException;
use OmnivaApi\Person;

/**
 *
 */
class Sender extends Person
{
    public function __construct($shipping_type = false)
    {
        parent::__construct($shipping_type);
    }

    public function generateSender()
    {
        if (!$this->company_name) throw new OmnivaApiException('All the fields must be filled. company_name is missing.');
        if (!$this->contact_name) throw new OmnivaApiException('All the fields must be filled. contact_name is missing.');
        if (!$this->street_name) throw new OmnivaApiException('All the fields must be filled. street_name is missing.');
        if (!$this->zipcode) throw new OmnivaApiException('All the fields must be filled. zipcode is missing.');
        if (!$this->city) throw new OmnivaApiException('All the fields must be filled. city is missing.');
        if (!$this->phone_number) throw new OmnivaApiException('All the fields must be filled. phone_number is missing.');
        if (!$this->country_id) throw new OmnivaApiException('All the fields must be filled. country_id is missing.');
        $sender = array(
            'shipping_type' => $this->shipping_type,
            'company_name' => $this->company_name,
            'contact_name' => $this->contact_name,
            'street_name' => $this->street_name,
            $this->shipping_type === 'courier' ?
              'zipcode' :
              'terminal_zipcode' => $this->zipcode,
            'city' => $this->city,
            'phone' => $this->phone_number,
            'country_id' => $this->country_id,
        );
        if ($this->shipping_type)
            $sender += ['shipping_type' => $this->shipping_type];
        return $sender;
    }

    public function generateSenderOffers()
    {
        if (!$this->zipcode) throw new OmnivaApiException('All the fields must be filled. zipcode is missing.');
        if (!$this->country_id) throw new OmnivaApiException('All the fields must be filled. country_id is missing.');
        return array(
            'zipcode' => $this->zipcode,
            'country_id' => $this->country_id
        );
    }

    public function returnJson()
    {
        return $this->json_encode(generateSender());
    }

    public function __toArray()
    {
        return $this->generateSender();
    }
}
