<?php

namespace OmnivaApi;

use OmnivaApi\Exception\OmnivaApiException;
use OmnivaApi\Exception\ValidationException;

class API
{
    protected $url = "https://tarptautines.mijora.lt/api/v1/";
    protected $token;
    private $debug_mode;

    public function __construct($token = false, $test_mode = false, $api_debug_mode = false)
    {
        if (!$token) {
            throw new OmnivaApiException("User Token is required");
        }

        $this->token = $token;

        if (!$test_mode) {
            $this->url = "https://tarptautines.omniva.lt/api/v1/";
        }

        if ($api_debug_mode) {
            $this->debug_mode = $api_debug_mode;
        }
    }

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

  public function setUrl($url)
  {
    $this->url = $url;

    return $this;
  }

    private function callAPI($url, $data = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $this->token
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($this->debug_mode) {
            echo '<b>Token:</b><br><br>';
            echo $this->token;
            echo '<br><br>';
            echo '<b>Endpoint:</b><br><br>';
            echo $url;
            echo '<br><br>';
            echo '<b>Method:</b><br><br>';
            echo debug_backtrace()[1]['function'] . '()';
            echo '<br><br>';
            echo '<b>Data passed:</b><br><br>';
            echo json_encode($data, JSON_PRETTY_PRINT);
            echo '<br><br>';
            echo '<b>Data returned:</b><br><br>';
            echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo '<br><br>';
            echo '<b>Default API lib response:</b><br><br>';
        }

        echo $this->debug_mode ? '<br><br>---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br><br>' : '';

        return $this->handleApiResponse($response, $httpCode);
    }

    private function handleApiResponse($response, $httpCode)
    {
        $respObj = json_decode($response, true);
        if ($httpCode == 200) {
          if (isset($respObj['messages']) && $respObj['messages']) {
              // istrinti sita eilute, kad vartotojui neisvestu
			  if ($this->debug_mode) {
				echo 'messages from ' . debug_backtrace()[2]['function'] . '():<br><br>';
			  }
              $this->throwErrors($respObj['messages']);
          }
            return json_decode($response)->result;
        }

        if ($httpCode == 401) {
            // galetu buti tikslesnis exception - Siusk24NotAuthorizedException
            throw new OmnivaApiException(implode(" \n", json_decode($response)->errors));
        }

        if (isset($respObj['errors']) && $respObj['errors']) {
			if ($this->debug_mode) {
				echo 'errors in ' . debug_backtrace()[2]['function'] . '():<br><br>';
			}
            $this->throwErrors($respObj['errors']);
        }

        $r = $response ? json_encode($response) : 'Connection timed out';
        throw new OmnivaApiException('API responded with error:<br><br>' . 'errors in ' . debug_backtrace()[2]['function'] . '():<br><br>' . $r);
    }


    private function throwErrors(array $arr)
    {
        $errs = [];

        $keys = array_keys($arr);
        for ($i = 0; $i < count($arr); $i++) {
            // 133-136 iskelti i atskira funkcija
            if (is_array($arr[$keys[$i]]))
                foreach ($arr[$keys[$i]] as $err)
                    array_push($errs, $keys[$i] . '->' . $err);
            else array_push($errs, $arr[$keys[$i]]);
        }

        throw new ValidationException(implode(",<br>", $errs));
    }

  public function listAllCountries()
  {
    $response = $this->callAPI($this->url . 'countries');

    return $response->countries;
  }

  public function listAllStates()
  {
    $response = $this->callAPI($this->url . 'states');

    return $response->states;
  }

  public function listAllServices()
  {
    $response = $this->callAPI($this->url . 'services');

    return $response->services;
  }

  public function getOffers(Sender $sender, Receiver $receiver, $parcels)
  {
    $post_data = array(
      'sender' => $sender->generateSenderOffers(),
      'receiver' => $receiver->generateReceiverOffers(),
      'parcels' => $parcels
    );
    $response = $this->callAPI($this->url . 'services/', $post_data);

    return $response->offers;
  }

  public function getAllOrders()
  {
    return $this->callAPI($this->url . 'orders');
  }

  public function generateOrder($order)
  {
    $post_data = $order->__toArray();
    return $this->callAPI($this->url . 'orders', $post_data);

  }

  public function generateOrder_parcelTerminal($order)
  {
    $post_data = $order->__toArray();

    return $this->callAPI($this->url . 'orders', $post_data);
  }

  public function cancelOrder($shipment_id)
  {
    return $this->callAPI($this->url . 'orders/' . $shipment_id . '/cancel');
  }

  public function getLabel($shipment_id)
  {
    return $this->callAPI($this->url . "orders/" . $shipment_id . "/label");
  }

  public function trackOrder($shipment_id)
  {
    return $this->callAPI($this->url . 'orders/' . $shipment_id . '/track');

  }

  public function generateManifest($cart_id)
  {
    return $this->callAPI($this->url . 'manifests/' . $cart_id);
  }

  public function generateManifestLatest()
  {
    return $this->callAPI($this->url . 'manifests/latest');
  }

  public function getTerminals($country_code = 'ALL')
  {
	if ($country_code == 'ALL'){
	  return $this->callAPI($this->url . 'terminals');
	}
    return $this->callAPI($this->url . 'terminals/' . $country_code);
  }
}
