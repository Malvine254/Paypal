<?php
require 'vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

// PayPal API credentials
$clientId = "Ac-L5ddC3L6IEWPodnswQtN24F4LiDDhRKi0raDHl_pHUExT1u17KVNJV1ohZDI2c9tX7x9rNFCpKU4A";
$clientSecret = "EL6LLdkLbuQL-kVY1OpZi9xSY_rxOsxEzi4bIc4N-wFdtEXH4B8waINkXSufzMGpVIqnLAdOJvxu343j";

// Set up PayPal client
$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);

// Start output buffering to prevent any "headers already sent" issues
ob_start();

// Check if the script is handling the return after approval
if (isset($_GET['orderId'])) {
    // Capture the payment
    $orderId = $_GET['orderId'];

    $captureRequest = new OrdersCaptureRequest($orderId);
    $captureRequest->prefer('return=representation');

    try {
        $captureResponse = $client->execute($captureRequest);
        echo "Payment Captured Successfully!<br>";
        echo "Capture ID: " . $captureResponse->result->id . "<br>";
        echo "Status: " . $captureResponse->result->status . "<br>";
    } catch (Exception $e) {
        echo "Error capturing payment: " . $e->getMessage() . "<br>";
    }

    ob_end_flush(); // Send output to the browser
    exit();
}

// Create a new order
$request = new OrdersCreateRequest();
$request->prefer('return=representation');
$request->body = [
    "intent" => "CAPTURE",
    "purchase_units" => [
        [
            "amount" => [
                "currency_code" => "USD",
                "value" => "0.20"
            ]
        ]
    ]
];

try {
    // Execute the order creation request
    $response = $client->execute($request);

    // Retrieve the approval URL and redirect the user
    foreach ($response->result->links as $link) {
        if ($link->rel === "approve") {
            header("Location: " . $link->href);
            ob_end_flush(); // Send any buffered output before redirecting
            exit();
        }
    }
} catch (Exception $e) {
    // Handle errors
    echo "Error creating order: " . $e->getMessage() . "<br>";
    ob_end_flush();
    exit();
}
?>
