<?php
require 'vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\LiveEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

if (isset($_POST['create_order'])) {
    // PayPal Live API credentials
    $clientId = "AcVSO26RLnvRRBvU2Qk1siReT_vlk9KIR-77DMeV7K4JD_Xkt7zXxZFhP48RCXjDZK8OmAetTIyIErMj";
    $clientSecret = "EII67bD0IKKC1aBrbqfd9wN_b5h7qqCsvFwdKDIo-1Jyf4hu00KIlx4IYm_XPq5yAKArAv2PipVvje4B";

    // Set up PayPal client
    $environment = new LiveEnvironment($clientId, $clientSecret);
    $client = new PayPalHttpClient($environment);

    $amount = $_POST['amount'];

    // Create a new order
    $request = new OrdersCreateRequest();
    $request->prefer('return=representation');
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
            "return_url" => "https://hr.armely.com/payment_success.php", // Ensure HTTPS
            "cancel_url" => "https://hr.armely.com/payment_cancel.php" // Ensure HTTPS
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
        echo "<h1>Error Creating Order</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        exit();
    }
}
?>
