<?php
require 'vendor/autoload.php';

// Import the required PayPal classes
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

if (isset($_POST['create_order'])) {
    // PayPal credentials
    $clientId = "AcVSO26RLnvRRBvU2Qk1siReT_vlk9KIR-77DMeV7K4JD_Xkt7zXxZFhP48RCXjDZK8OmAetTIyIErMj";
    $clientSecret = "EII67bD0IKKC1aBrbqfd9wN_b5h7qqCsvFwdKDIo-1Jyf4hu00KIlx4IYm_XPq5yAKArAv2PipVvje4B";

    // Create PayPal Environment
    $environment = new ProductionEnvironment($clientId, $clientSecret);
    $client = new PayPalHttpClient($environment);

    // Retrieve the amount from the POST request
    $amount = $_POST['amount'];

    // Create a new order
    $request = new OrdersCreateRequest();
    $request->prefer('return=representation'); // Request full response
    $request->body = [
        "intent" => "CAPTURE",
        "purchase_units" => [
            [
                "amount" => [
                    "currency_code" => "USD",
                    "value" => $amount
                ]
            ]
        ],
        "application_context" => [
            "return_url" => "https://armely.com/payment_success.php", // Replace with your success URL
            "cancel_url" => "https://armely.com/payment_cancel.php"   // Replace with your cancel URL
        ]
    ];

    try {
        // Execute the order creation request
        $response = $client->execute($request);

        // Retrieve the approval URL and redirect the user
        foreach ($response->result->links as $link) {
            if ($link->rel === "approve") {
                header("Location: " . $link->href);
                exit();
            }
        }
    } catch (Exception $e) {
        // Handle errors
        echo "<h1>Error Creating Order</h1>";
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        exit();
    }
}
?>
