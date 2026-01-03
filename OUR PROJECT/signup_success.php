<?php
session_start();
require_once "config.php";

if (!isset($_GET['data'])) { http_response_code(400); exit("Missing payment response data."); }
$payload = json_decode(base64_decode($_GET['data']), true);
if (!is_array($payload) || !isset($payload['status'], $payload['transaction_uuid'], $payload['total_amount'])) {
    http_response_code(400); exit("Invalid payment payload.");
}

$status         = $payload['status'];
$txnUuid        = $payload['transaction_uuid'];
$totalAmount    = (float)$payload['total_amount'];
$gatewayTxnCode = $payload['transaction_code'] ?? null;

function render_page($ok, $title, $subtitle, $details = []) {
    $accent  = $ok ? "#22c55e" : "#ef4444";
    $badgeBg = $ok ? "rgba(34,197,94,0.12)" : "rgba(239,68,68,0.12)";
    include('header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        body { margin:0; font-family:"Segoe UI",sans-serif; background:#0b1222; color:#e2e8f0; }
        .content-shell { max-width:980px; margin:40px auto; padding:0 20px; }
        .card { background:linear-gradient(145deg, rgba(17,24,39,0.95), rgba(15,23,42,0.95)); padding:32px; border-radius:16px; border:1px solid #1f2937; box-shadow:0 25px 70px rgba(0,0,0,0.45); }
        .badge { display:inline-block; padding:8px 14px; border-radius:999px; background:<?php echo $badgeBg; ?>; color:<?php echo $accent; ?>; font-weight:700; letter-spacing:0.4px; margin-bottom:14px; }
        h1 { margin:0 0 10px; font-size:30px; color:#f8fafc; }
        .subtitle { margin:0 0 18px; color:#cbd5e1; font-size:16px; }
        .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); gap:12px; margin-top:18px; }
        .stat { background:#0b1629; border:1px solid #16233b; border-radius:12px; padding:12px 14px; }
        .label { font-size:12px; text-transform:uppercase; letter-spacing:0.8px; color:#94a3b8; margin-bottom:6px; }
        .value { font-size:16px; color:#e2e8f0; word-break:break-all; }
        a.btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; margin-top:24px; padding:12px 18px; border-radius:12px; text-decoration:none; background:#2563eb; color:#fff; font-weight:700; box-shadow:0 10px 30px rgba(37,99,235,0.3); }
    </style>
</head>
<body>
<div class="content-shell">
    <div class="card">
        <div class="badge"><?php echo $ok ? "Payment Success" : "Payment Issue"; ?></div>
        <h1><?php echo htmlspecialchars($title); ?></h1>
        <div class="subtitle"><?php echo htmlspecialchars($subtitle); ?></div>
        <?php if (!empty($details)) : ?>
            <div class="grid">
                <?php foreach ($details as $label => $value): ?>
                    <div class="stat">
                        <div class="label"><?php echo htmlspecialchars($label); ?></div>
                        <div class="value"><?php echo htmlspecialchars($value); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <a class="btn" href="index.php">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
<?php
    exit;
}

if ($status !== "COMPLETE") {
    render_page(false, "Payment Not Completed", "We could not confirm your payment.", [
        "Status" => $status,
        "Transaction" => $txnUuid
    ]);
}

// update orders
$stmt = $link->prepare(
    "UPDATE orders SET Status='COMPLETE', TransactionCode=?, UpdatedDate=NOW() WHERE TransactionUniqueId=?"
);
$stmt->bind_param("ss", $gatewayTxnCode, $txnUuid);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();
$link->close();

if ($affected < 1) {
    render_page(false, "Record Not Found", "Please contact support with this code.", [
        "Transaction" => $txnUuid
    ]);
}

render_page(true, "Signup Payment Successful!", "Your spot is confirmed. See you at the event!", [
    "Amount" => "NPR " . number_format($totalAmount, 2),
    "Transaction" => $txnUuid,
    "Gateway Ref" => $gatewayTxnCode ?: "Not provided"
]);
