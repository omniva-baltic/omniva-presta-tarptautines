<?php

namespace OmnivaApi;

use OmnivaApi\Exception\OmnivaApiException;

/**
 *
 */
class Parcel
{
    private $amount;
    private $unit_weight;
    private $width;
    private $length;
    private $height;

    public function __construct()
    {

    }

    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function setUnitWeight($unit_weight)
    {
        $this->unit_weight = $unit_weight;

        return $this;
    }

    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    public function generateParcel()
    {
        if (!$this->amount) throw new OmnivaApiException('All the fields must be filled. amount is missing.');
        if (!$this->unit_weight) throw new OmnivaApiException('All the fields must be filled. unit_weight is missing.');
        if (!$this->width) throw new OmnivaApiException('All the fields must be filled. width is missing.');
        if (!$this->length) throw new OmnivaApiException('All the fields must be filled. length is missing.');
        if (!$this->height) throw new OmnivaApiException('All the fields must be filled. heigth is missing.');
        $parcel = array(
            'amount' => $this->amount,
            'weight' => $this->unit_weight,
            'x' => $this->width,
            'y' => $this->length,
            'z' => $this->height
        );

        return $parcel;
    }


    public function returnJson()
    {
        return json_encode($this->generateParcel());
    }

    public function __toArray()
    {
        return $this->generateParcel();
    }
}
