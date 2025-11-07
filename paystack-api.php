<?php
session_start();
require_once "config/db_config.php";

// Ensure only logged-in students can pay
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $fee_id = $_POST['fee_id'];
    $email = $_POST['email'];
    $amount = $_POST['amount'];

    // Convert amount to kobo (Paystack requires amount * 100)
    $amount_kobo = $amount * 100;

    // ✅ Paystack Secret Key (LIVE)
    $secret_key = "sk_test_49f06d003fbebc5f61779d48c49db15d2050bc56"; // 🔒 Replace with your live key

    // ✅ Callback URL
    $callback_url = "https://eagletechafrica.com/ashabakari/paystack-callback.php";
    $cancel_url = "https://eagletechafrica.com/ashabakari/paystack-cancelled.php"; 


    // Initialize Paystack payment
    $url = "https://api.paystack.co/transaction/initialize";
    $fields = [
        'email' => $email,
        'amount' => $amount_kobo,
        'callback_url' => $callback_url,
        'metadata' => json_encode([
            'user_id' => $user_id,
            'fee_id' => $fee_id,
            'amount' => $amount,
            'cancel_url' => $cancel_url // 

        ])
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $secret_key",
        "Cache-Control: no-cache"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        die("Curl error: " . curl_error($ch));
    }

    curl_close($ch);
    $response = json_decode($result, true);

    if (isset($response['status']) && $response['status'] === true) {
        header("Location: " . $response['data']['authorization_url']);
        exit;
    } else {
        echo "Error initializing payment: " . $response['message'];
    }
} else {
    echo "Invalid request.";
}
