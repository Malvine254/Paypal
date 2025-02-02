<?php
require 'vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

// PayPal API credentials (Production)
$clientId = "AcVSO26RLnvRRBvU2Qk1siReT_vlk9KIR-77DMeV7K4JD_Xkt7zXxZFhP48RCXjDZK8OmAetTIyIErMj";
$clientSecret = "EII67bD0IKKC1aBrbqfd9wN_b5h7qqCsvFwdKDIo-1Jyf4hu00KIlx4IYm_XPq5yAKArAv2PipVvje4B";

// Set up PayPal client with Production Environment
$environment = new ProductionEnvironment($clientId, $clientSecret);
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
            foreach ($response->result->purchase_units as $unit) {
                foreach ($unit->payments->captures as $capture) {
                    echo "<p>Capture ID: " . htmlspecialchars($capture->id) . "</p>";
                    echo "<p>Status: " . htmlspecialchars($capture->status) . "</p>";
                    echo "<p>Amount: " . htmlspecialchars($capture->amount->currency_code . " " . $capture->amount->value) . "</p>";
                }
            }
        } else {
            echo "<h1>Payment Not Completed</h1>";
            echo "<p>Status: " . htmlspecialchars($response->result->status) . "</p>";
        }
    } catch (Exception $e) {
        // Handle capture errors
        echo "<h1>Error Capturing Payment</h1>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<h1>Error</h1>";
    echo "<p>No token found in the URL.</p>";
}
?>
