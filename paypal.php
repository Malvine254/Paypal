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

// Check if we are handling the return from PayPal
if (isset($_GET['token'])) {
    handlePaymentCapture($client, $_GET['token']);
} else {
    createPayPalOrder($client);
}

/**
 * Handles the capture of a PayPal payment after the buyer approves it.
 *
 * @param PayPalHttpClient $client
 * @param string $orderId
 */
function handlePaymentCapture($client, $orderId)
{
    $captureRequest = new OrdersCaptureRequest($orderId);
    $captureRequest->prefer('return=representation');

    try {
        $response = $client->execute($captureRequest);

        // Check the payment status
        if ($response->result->status === "COMPLETED") {
            echo "<h1>Payment Successful</h1>";
            echo "<p>Order ID: " . htmlspecialchars($orderId) . "</p>";
            echo "<p>Capture ID: " . htmlspecialchars($response->result->id) . "</p>";
            echo "<p>Status: " . htmlspecialchars($response->result->status) . "</p>";
        } else {
            echo "<h1>Payment Not Completed</h1>";
            echo "<p>Status: " . htmlspecialchars($response->result->status) . "</p>";
        }
    } catch (Exception $e) {
        echo "<h1>Error Capturing Payment</h1>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    exit();
}

/**
 * Creates a new PayPal order and redirects the user to PayPal for approval.
 *
 * @param PayPalHttpClient $client
 */
function createPayPalOrder($client)
{
    $request = new OrdersCreateRequest();
    $request->prefer('return=representation');
    $request->body = [
        "intent" => "CAPTURE",
        "purchase_units" => [
            [
                "amount" => [
                    "currency_code" => "USD",
                    "value" => "10.00"
                ]
            ]
        ],
        "application_context" => [
            "return_url" => "http://yourwebsite.com/payment_success.php", // Replace with your success URL
            "cancel_url" => "http://yourwebsite.com/payment_cancel.php"  // Replace with your cancel URL
        ]
    ];

    try {
        $response = $client->execute($request);

        // Retrieve the approval URL and redirect the user
        foreach ($response->result->links as $link) {
            if ($link->rel === "approve") {
                header("Location: " . $link->href);
                exit();
            }
        }
    } catch (Exception $e) {
        echo "<h1>Error Creating Order</h1>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        exit();
    }
}
?>
