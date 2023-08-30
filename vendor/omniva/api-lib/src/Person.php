<?php

namespace OmnivaApi;

use OmnivaApi\Exception\OmnivaApiException;

/**
 *
 */
class Person
{
    protected $company_name;
    protected $contact_name;
    protected $street_name;
    protected $zipcode;
    protected $terminal_id;
    protected $city;
    protected $phone_number;
    protected $country_id;
    protected $eori;
    protected $hs_code;
    protected $state_code;

    protected $shipping_type;

    const SHIPPING_TERMINAL = 'terminal';
    const SHIPPING_COURIER = 'courier';

    // galetu buti protected, nes naudojamas tik sitame faile
    public $valid_shipping_types;

    public function __construct($shipping_type)
    {
        $this->valid_shipping_types = array(
            self::SHIPPING_COURIER,
            self::SHIPPING_TERMINAL,
            false
        );
        $this->setShippingType($shipping_type);
    }

    public function setShippingType($shipping_type)
    {
        if (!in_array($shipping_type, $this->valid_shipping_types)) {
            throw new OmnivaApiException('Unknown shipping type:<br>' . $shipping_type . '. You need to use one of the following types:<br><br>' . implode("<br>", $this->valid_shipping_types));
        }
        $this->shipping_type = $shipping_type;


        return $this;
    }

    public function setCompanyName(string $company_name)
    {
        $this->company_name = $company_name;

        return $this;
    }

    public function setContactName(string $contact_name)
    {
        $this->contact_name = $contact_name;

        return $this;
    }

    public function setStreetName(string $street_name)
    {
        $this->street_name = $street_name;

        return $this;
    }

    public function setZipcode(string $zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function setTerminalId(string $terminal_id)
    {
        $this->terminal_id = $terminal_id;

        return $this;
    }

    public function setCity(string $city)
    {
        $this->city = $city;

        return $this;
    }

    public function setPhoneNumber(string $phone_number)
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function setCountryId($country_id)
    {
        $this->country_id = $country_id;

        return $this;
    }

    public function setEori($eori)
    {
      $this->eori = $eori;

      return $this;
    }

    public function setHsCode($hs_code)
    {
      $this->hs_code = $hs_code;

      return $this;
    }

    public function setStateCode($state_code)
    {
      $this->state_code = $state_code;

      return $this;
    }
}
