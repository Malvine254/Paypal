<?php
require 'vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
if (isset($_POST['create_order'])) {
    

    // PayPal API credentials
    $clientId = "Ac-L5ddC3L6IEWPodnswQtN24F4LiDDhRKi0raDHl_pHUExT1u17KVNJV1ohZDI2c9tX7x9rNFCpKU4A";
    $clientSecret = "EL6LLdkLbuQL-kVY1OpZi9xSY_rxOsxEzi4bIc4N-wFdtEXH4B8waINkXSufzMGpVIqnLAdOJvxu343j";

    // Set up PayPal client
    $environment = new SandboxEnvironment($clientId, $clientSecret);
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
        "return_url" => "http://hr.armely.com/payment_success.php",
        "cancel_url" => "http://hr.armely.com/payment_success.php"// Replace with your cancel URL
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
