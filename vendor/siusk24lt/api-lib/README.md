 # Siusk24LT API-lib

Its a library for Siusk24LT API.

## Using Siusk24LT API-lib
- ```__PATH_TO_LIB__``` is a path where Siusk24LT API is placed. This will load Siusk24LT namespace
```php
require(__PATH_TO_LIB__ . "Siusk24LT/vendor/autoload.php");
```

Validations, checks, etc. throws `Siusk24LTException` and calls to library classes should be wrapped in: blocks
```php
try {
  // ...
} catch (Siusk24LTException $e) {
  // ...
}
```

Any function starting with `add` or `set` returns its class so functions can be chained.

## Authentication
---
Uses supplied user `$token`. It is called during API object creation.
- Initialize new API library using: `$ps = new API($token);`
- Set new token using: `$ps->setToken($token);`


## Creating and Editing Sender
---
`use Siusk24LT\Sender;` will allow creating Sender object.

Minimum required setup:

```php
use Siusk24LT\Sender;

$sender = new Sender();

$sender
  ->setCompanyName('company_name')
  ->setContactName('contact_name')
  ->setStreetName('street_name')
  ->setZipcode('zipcode')
  ->setCity("city")
  ->setPhoneNumber('phone_number')
  ->setCountryId('country_id');
```


## Creating Receiver
---
`use Siusk24LT\Receiver;` will allow to create Receiver object.

Minimum required setup:
- shipping type must be either "courier" or "terminal"

```php
use Siusk24LT\Receiver;

try {
  $receiver1 = new Receiver();

  $receiver2 = new Receiver();

  $receiver1
    ->setShippingType('terminal')
    ->setCompanyName('company_name')
    ->setContactName('contact_name')
    ->setStreetName('street_name')
    ->setZipcode('zipcode')
    ->setCity('city')
    ->setPhoneNumber('phone_number')
    ->setCountryId('country_id');

  $receiver2
    ->setShippingType('courier')
    ->setCompanyName('company_name')
    ->setContactName('contact_name')
    ->setStreetName('street_name')
    ->setZipcode('zipcode')
    ->setCity('city')
    ->setPhoneNumber('phone_number')
    ->setCountryId('country_id');

} catch (Siusk24LTException $e) {
  // Handle validation exceptions here
}
```

## Creating Parcel
---
`use Siusk24LT\Parcel;` will allow to create Parcel object.

Minimum required setup:

```php
use Siusk24LT\Parcel;

$parcel = new Parcel();=
$parcel
    ->setAmount(2)
    ->setUnitWeight(1)
    ->setWidth(20)
    ->setLength(20)
    ->setHeight(20);
```

## Creating Item
---
`use Siusk24LT\Item;` will allow to create Item object.

Minimum required setup:

```php
// apacioje du zemiau use istrinti ir naudoti use Siusk24LT\Item;
use Siusk24LT\Item;
use Siusk24LT\Sender;

$item = new Item();
$item
  ->setDescription('description')
  ->setItemPrice(5)
  ->setItemAmount(1)
```


## Creating Order
---

```php
// API use nera panaudotas - galima istrinti
use Siusk24LT\API;
use Siusk24LT\Sender;
use Siusk24LT\Receiver;
use Siusk24LT\Item;
use Siusk24LT\Parcel;
use Siusk24LT\Order;

$sender = new Sender();
$sender
    ->setCompanyName('TEST')
    ->setContactName('TEST')
    ->setStreetName('TEST')
    ->setZipcode('48311')
    ->setCity("TEST")
    ->setPhoneNumber('37061234567')
    ->setCountryId('122');

$receiver = new Receiver('courier');
$receiver
    ->setCompanyName('TEST')
    ->setContactName('TEST')
    ->setStreetName('TEST')
    ->setZipcode('12345')
    ->setCity('TEST')
    ->setPhoneNumber('+37061234567')
    ->setCountryId('122');

$parcel1 = new Parcel();
$parcel1
        ->setAmount(2)
        ->setUnitWeight(1)
        ->setWidth(20)
        ->setLength(20)
        ->setHeight(20);

$parcel2 = new Parcel();
$parcel2
        ->setAmount(3)
        ->setUnitWeight(2)
        ->setWidth(20)
        ->setLength(20)
        ->setHeight(20);

$parcels = array($parcel1, $parcel2);

$item1 = new Item();
$item1
    ->setDescription('test package')
    ->setItemPrice(5)
    ->setItemAmount(1);
$item2 = new Item();
$item2
    ->setDescription('test package')
    ->setItemPrice(1)
    ->setItemAmount(3);

$items = array($item1, $item2);

$callback_urls = array("www.1.com/cb", "www.2.com/cb");

$order = new Order();

$order
  ->setServiceCode($service_code)
  ->setSender($sender)
  ->setReceiver($receiver)
  ->setParcels($parcels)
  ->setReference($reference)
  ->setItems($items)
  ->setCallbackUrls($callback_urls);
```

When creating Order it is possible to register a single item or parcel, by passing it without array.
`$order->setParcels($parcel1)->setItems($item1);`
It is also possible to add additional items

## Calling API
---
- check **src/examples/index.php** for Calling this API examples.
