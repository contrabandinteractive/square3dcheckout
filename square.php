<?php


require 'vendor/autoload.php';

use Square\SquareClient;
use Square\Environment;
use Square\Exceptions\ApiException;
use Square\Models\Money;


/*
$client = new SquareClient([
    'accessToken' => '',
    'environment' => Environment::SANDBOX,
]);
*/

$client = new SquareClient([
    'accessToken' => '',
    'environment' => Environment::PRODUCTION,
]);

$thelocation;

try {

    $apiResponse = $client->getLocationsApi()->listLocations();

    if ($apiResponse->isSuccess()) {
        $result = $apiResponse->getResult();
        foreach ($result->getLocations() as $location) {
            printf(
                "%s: %s, %s, %s<p/>",
                $location->getId(),
                $location->getName(),
                $location->getAddress()->getAddressLine1(),
                $location->getAddress()->getLocality()
            );
            $thelocation = $location->getId();
        }

    } else {
        $errors = $apiResponse->getErrors();
        foreach ($errors as $error) {
            printf(
                "%s<br/> %s<br/> %s<p/>",
                $error->getCategory(),
                $error->getCode(),
                $error->getDetail()
            );
        }
    }

} catch (ApiException $e) {
    echo "ApiException occurred: <b/>";
    echo $e->getMessage() . "<p/>";
}

$price_money = new \Square\Models\Money();
$price_money->setAmount(3000);
$price_money->setCurrency('USD');

$quick_pay = new \Square\Models\QuickPay(
    'Vinyl Record',
    $price_money,
    $thelocation
);

$body = new \Square\Models\CreatePaymentLinkRequest();
$idkey = uniqid();
$body->setIdempotencyKey($idkey);
$body->setQuickPay($quick_pay);

$api_response = $client->getCheckoutApi()->createPaymentLink($body);

if ($api_response->isSuccess()) {
    $result = $api_response->getResult();

    $json = json_encode($result);
    $json2 = json_decode($json);

    header("Location: ".$json2->payment_link->url);


} else {
    $errors = $api_response->getErrors();
}
