<?php

namespace OmnivaApi;

use OmnivaApi\Exception\OmnivaApiException;

/**
 *
 */
class Item
{
    private $description;
    private $value;
    private $units;
    private $country_id;

    public function __construct()
    {

    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function setItemPrice($value)
    {
        $this->value = $value;

        return $this;
    }

    public function setItemAmount($units)
    {
        $this->units = $units;

        return $this;
    }

    public function setCountryId($country_id)
    {
      $this->country_id = $country_id;

      return $this;
    }

    public function generateItem()
    {
        if (!$this->description) throw new OmnivaApiException('All the fields must be filled. description is missing.');
        if (!$this->value) throw new OmnivaApiException('All the fields must be filled. value is missing.');
        if (!$this->units) throw new OmnivaApiException('All the fields must be filled. units is missing.');
        if (!$this->country_id) throw new OmnivaApiException('All the fields must be filled. country_id is missing.');
        return array(
            'description' => $this->description,
            'value' => $this->value,
            'units' => $this->units,
            'country_id' => $this->country_id
        );
    }

    public function returnObject()
    {
        return $this->generateItem();
    }

    public function returnJson()
    {
        return json_encode($this->generateItem());
    }

    public function __toArray()
    {
        return $this->generateItem();
    }
}
