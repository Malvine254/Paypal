<?php
require 'vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

// PayPal API credentials
$clientId = "Ac-L5ddC3L6IEWPodnswQtN24F4LiDDhRKi0raDHl_pHUExT1u17KVNJV1ohZDI2c9tX7x9rNFCpKU4A";
$clientSecret = "EL6LLdkLbuQL-kVY1OpZi9xSY_rxOsxEzi4bIc4N-wFdtEXH4B8waINkXSufzMGpVIqnLAdOJvxu343j";

// Set up PayPal client
$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);

// Check for the token in the URL
if (isset($_GET['token'])) {
    $orderId = $_GET['token']; // The token represents the PayPal order ID

    // Create a request to capture the order
    $captureRequest = new OrdersCaptureRequest($orderId);
    $captureRequest->prefer('return=representation');

    try {
        // Execute the capture request
        $response = $client->execute($captureRequest);

        // Check if the payment is completed
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
        // Handle capture errors
        echo "<h1>Error Capturing Payment</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<h1>Error</h1>";
    echo "<p>No token found in the URL.</p>";
}
?>
