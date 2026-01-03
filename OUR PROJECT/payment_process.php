<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("location: index.php"); exit; }

$userId  = $_SESSION['id'] ?? null;
$type    = $_POST['payment_type'] ?? null;
$amount  = (float)($_POST['amount'] ?? 0);
$txnUuid = "VMS-" . bin2hex(random_bytes(6)); // unique per payment

$product_code = "EPAYTEST";
$secret       = "8gBm/:&EnhH.1/q";

switch ($type) {
    case 'signup':
        $eventId  = (int)$_POST['event_id'];
        $signupId = (int)$_POST['signup_id'];
        $stmt = $link->prepare(
            "INSERT INTO orders (EventId, SignupId, TransactionUniqueId, Amount, Status, UserId)
             VALUES (?, ?, ?, ?, 'PENDING', ?)"
        );
        $stmt->bind_param("iisdi", $eventId, $signupId, $txnUuid, $amount, $userId);
        $successUrl = "http://localhost/volunteering-management-system/OUR%20PROJECT/signup_success.php";
        $failureUrl = "http://localhost/volunteering-management-system/OUR%20PROJECT/failure.php?type=signup&txn={$txnUuid}";
        break;

    case 'donation':
        $charityId = (int)$_POST['charity_id'];
        $stmt = $link->prepare(
            "INSERT INTO donations (CharityId, TransactionUniqueId, Amount, Status, UserId)
             VALUES (?, ?, ?, 'PENDING', ?)"
        );
        $stmt->bind_param("isdi", $charityId, $txnUuid, $amount, $userId);
        $successUrl = "http://localhost/volunteering-management-system/OUR%20PROJECT/donation_success.php";
        $failureUrl = "http://localhost/volunteering-management-system/OUR%20PROJECT/donate_action.php?status=failed&txn={$txnUuid}";
        break;

    default:
        exit("Invalid payment_type");
}
$stmt->execute();
$stmt->close();
$link->close();

$message   = "total_amount=$amount,transaction_uuid=$txnUuid,product_code=$product_code";
$signature = base64_encode(hash_hmac('sha256', $message, $secret, true));
?>
<form id="esewa_form" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
    <input type="hidden" name="amount" value="<?php echo $amount; ?>">
    <input type="hidden" name="tax_amount" value="0">
    <input type="hidden" name="total_amount" value="<?php echo $amount; ?>">
    <input type="hidden" name="transaction_uuid" value="<?php echo $txnUuid; ?>">
    <input type="hidden" name="product_code" value="<?php echo $product_code; ?>">
    <input type="hidden" name="product_service_charge" value="0">
    <input type="hidden" name="product_delivery_charge" value="0">
    <input type="hidden" name="success_url" value="<?php echo $successUrl; ?>">
    <input type="hidden" name="failure_url" value="<?php echo $failureUrl; ?>">
    <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
    <input type="hidden" name="signature" value="<?php echo $signature; ?>">
</form>
<script>document.getElementById('esewa_form').submit();</script>
