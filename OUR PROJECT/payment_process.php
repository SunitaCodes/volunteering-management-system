<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $charity_id = $_POST['charity_id'];
    $transaction_uuid = "VMS-" . time(); 

    $_SESSION['temp_donation'] = [
        'amount' => $amount,
        'charity_id' => $charity_id
    ];

    $product_code = "EPAYTEST";
    $secret = "8gBm/:&EnhH.1/q";
    
    $message = "total_amount=$amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
    $s = hash_hmac('sha256', $message, $secret, true);
    $signature = base64_encode($s);
?>
    <form id="esewa_form" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
        <input type="hidden" name="tax_amount" value="0">
        <input type="hidden" name="total_amount" value="<?php echo $amount; ?>">
        <input type="hidden" name="transaction_uuid" value="<?php echo $transaction_uuid; ?>">
        <input type="hidden" name="product_code" value="<?php echo $product_code; ?>">
        <input type="hidden" name="product_service_charge" value="0">
        <input type="hidden" name="product_delivery_charge" value="0">
        
        <input type="hidden" name="success_url" value="http://localhost/OUR%20PROJECT/success.php">
        <input type="hidden" name="failure_url" value="http://localhost/OUR%20PROJECT/donate_action.php">
        
        <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
        <input type="hidden" name="signature" value="<?php echo $signature; ?>">
    </form>
    <script>document.getElementById('esewa_form').submit();</script>
<?php } ?>