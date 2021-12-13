<!DOCTYPE html>
<html>
<body>
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);

require("../vendor/autoload.php");

use Siusk24LT\API;
use Siusk24LT\Sender;
use Siusk24LT\Receiver;
use Siusk24LT\Item;
use Siusk24LT\Parcel;
use Siusk24LT\Order;
use Siusk24LT\Exception\Siusk24LTException;

$token = "qk5yeuhSOE4XTJ3Upiq92Qtt";

try {
    $ps = new API($token, true, true);

    $sender1 = new Sender();
    $sender1
        ->setCompanyName('TESTAS')
        ->setContactName('TEST')
        ->setStreetName('TEST')
        ->setZipcode('50217')
        ->setCity("TEST")
        ->setPhoneNumber('+37061234567')
        ->setCountryId('122')
        ->setShippingType('courier');
    $sender2 = new Sender();
    $sender2
        ->setCompanyName('TEST')
        ->setContactName('TEST')
        ->setStreetName('TEST')
        ->setZipcode('48311')
        ->setCity("TEST")
        ->setPhoneNumber('37061234567')
        ->setCountryId('122');
    $sender3 = new Sender();
    $sender3
        ->setCompanyName('UAB ZIPAS')
        ->setContactName('Daiva Jagielaitė')
        ->setStreetName('Saltoniškių g. 10A')
        ->setZipcode('08105')
        ->setCity("Vilnius")
        ->setPhoneNumber('+37061697227')
        ->setCountryId('122');

    $receiver1 = new Receiver('courier');
    $receiver2 = new Receiver('terminal');
    $receiver3 = new Receiver('courier');

    $receiver1
        ->setShippingType('courier')
        ->setCompanyName('TEST')
        ->setContactName('TEST')
        ->setStreetName('TEST')
        ->setZipcode('12345')
        ->setCity('TEST')
        ->setPhoneNumber('+37061234567')
        ->setEori('GB987654312011')
        ->setCountryId('122');

    $receiver2
        ->setShippingType('terminal')
        ->setContactName('TEST')
        ->setZipcode('12-345')
        ->setPhoneNumber('+37061234567')
        ->setEori('GB987654312011')
        ->setCountryId('116');

    $receiver3
        ->setShippingType('courier')
        ->setCompanyName('UNO MOMENTO')
        ->setContactName('JOHN JOHN')
        ->setStreetName('James road 54')
        ->setZipcode('12-345')
        ->setCity('Frankfurt')
        ->setPhoneNumber('+37065032153')
        ->setEori('GB987654312011')
        ->setCountryId('241');

    $parcel1 = new Parcel();
    $parcel1
        ->setAmount(2)
        ->setUnitWeight(1)
        ->setWidth(20)
        ->setLength(20)
        ->setHeight(20);
    $parcels1 = array($parcel1->generateParcel());

    $item1 = new Item();
    $item1
        ->setDescription('test package')
        ->setItemPrice(5)
        ->setItemAmount(1)
        ->setCountryId('122');
    $item2 = new Item();
    $item2
        ->setDescription('test package')
        ->setItemPrice(1)
        ->setItemAmount(3)
        ->setCountryId('122');
    $items1 = array($item1->generateItem());

    $callback_urls = array(
        "http://webhook.site/1c05bdb9-6f15-4c61-8549-8f45c1cae1a0"
    );

    $order1 = new Order();
    $order1
        ->setServiceCode('S1')
        ->setSender($sender1)
        ->setReceiver($receiver1)
        ->setParcels($parcels1)
        ->setReference('test package')
        ->setItems($items1)
        ->setCallbackUrls($callback_urls);

    $order2 = new Order();
    $order2
        ->setServiceCode('S24EC0')
        ->setSender($sender1)
        ->setReceiver($receiver1)
        ->setParcels($parcels1)
        ->setReference('test package')
        ->setItems($items1)
        ->setCallbackUrls($callback_urls);
    $order3 = new Order();
    $order3
        ->setServiceCode('S24EC0')
        ->setSender($sender1)
        ->setReceiver($receiver1)
        ->setParcels($parcels1)
        ->setReference('test package')
        ->setItems($items1)
        ->setCallbackUrls($callback_urls);
    $order3->addItems($item2)->addItems($items1)->addParcels($parcel1)->addParcels($parcels1);

//  $allCountries                       = $ps->listAllCountries();
//  $allStates                        = $ps->listAllStates();
//  $allServices                        = $ps->listAllServices();
//  $offers                              = $ps->getOffers($sender1, $receiver1, $parcels1);
//  $allOrders                          = $ps->getAllOrders();
//  $generateOrderResult = $ps->generateOrder($order3);
//  $generateOrder_parcelTerminalResult = $ps->generateOrder_parcelTerminal($order2);
//  $cancelOrderResult = $ps->cancelOrder('S240423208227');
//  $label                              = $ps->getLabel('S240423208227');
//  $orderTrackingInfo                  = $ps->trackOrder('S240423208227');
//  $manifest                           = $ps->generateManifest('S240423208227');
//  $manifestLatest                     = $ps->generateManifest('S240423208227');
  $terminals                          = $ps->getTerminals('LT');




//    echo json_encode($allCountries);
//    echo json_encode($allStates);
//    echo json_encode($allServices);
//    echo json_encode($offers);
//    echo json_encode($allOrders);
//    echo json_encode($generateOrderResult);
//    echo json_encode($generateOrder_parcelTerminalResult);
//    echo json_encode($cancelOrderResult);
//    echo json_encode($label);
//    echo json_encode($orderTrackingInfo);
//    echo json_encode($manifest);
//    echo json_encode($manifestLatest);
//    echo json_encode($manifestLatest);
  echo json_encode($terminals);
} catch (Siusk24LTException $e) {
    echo $e->getMessage();
}

?>


</body>
</html>
