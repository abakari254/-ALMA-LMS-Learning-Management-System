<?php
session_start();
require_once "config/db_config.php";

// Verify Paystack payment
if (!isset($_GET['reference'])) {
    die("No reference supplied");
}

$reference = $_GET['reference'];
$secret_key = "sk_test_49f06d003fbebc5f61779d48c49db15d2050bc56"; // 🔒 Replace with your live key

// Verify transaction
$url = "https://api.paystack.co/transaction/verify/" . $reference;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $secret_key"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (!$result['status'] || $result['data']['status'] !== 'success') {
    echo "<h3 style='color:red;text-align:center;'>❌ Payment not successful.</h3>";
    exit;
}

// Retrieve metadata
$metadata = $result['data']['metadata'];
$user_id = $metadata['user_id'];
$fee_id = $metadata['fee_id'];
$amount = $metadata['amount'];
$reference = $result['data']['reference'];
$payment_date = date('Y-m-d H:i:s');

// Begin transaction
try {
    $db->beginTransaction();

    // Update user_fees
    $update = $db->prepare("UPDATE user_fees 
                            SET amount_paid = amount_paid + ?, balance = balance - ? 
                            WHERE user_id = ? AND fee_id = ?");
    $update->execute([$amount, $amount, $user_id, $fee_id]);

    // Record payment in payment_history
    $insert = $db->prepare("INSERT INTO payment_history (user_id, fee_id, amount_paid, payment_date, transaction_ref) 
                            VALUES (?, ?, ?, ?, ?)");
    $insert->execute([$user_id, $fee_id, $amount, $payment_date, $reference]);

    $db->commit();

    echo "<div style='text-align:center; font-family:Poppins;'>
            <h2 style='color:green;'>✅ Payment Successful!</h2>
            <p>Reference: <strong>{$reference}</strong></p>
            <p>Amount Paid: KES " . number_format($amount, 2) . "</p>
            <a href='students_dashboard.php' style='display:inline-block; margin-top:15px; padding:10px 20px; background:#1e2a78; color:#fff; border-radius:6px; text-decoration:none;'>Return to Dashboard</a>
          </div>";

} catch (Exception $e) {
    $db->rollBack();
    echo "Database Error: " . $e->getMessage();
}
