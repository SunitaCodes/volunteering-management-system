<?php
session_start();
require_once "config.php";

// 1. Protect the page: Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "volunteer"){
    header("location: login.php");
    exit;
}

// 2. Fetch Charities for the dropdown menu
$charities = [];
$sql_charities = "SELECT CharityId, Name FROM charities ORDER BY Name ASC";
if ($result = mysqli_query($link, $sql_charities)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $charities[] = $row;
    }
    mysqli_free_result($result);
}

// Pre-select charity if ID is passed in URL
$selected_charity_id = isset($_GET['charity_id']) ? (int)$_GET['charity_id'] : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate - Volunteering Management System</title>
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        .donation-wrapper { max-width: 500px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-esewa { background-color: #60bb46; color: white; border: none; padding: 12px; width: 100%; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-esewa:hover { background-color: #52a03b; }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="donation-wrapper">
        <div class="card donation-form-card">
            <h2 style="text-align: center;">💖 Make a Donation</h2>
            <p style="text-align: center; color: #666;">Your support changes lives. Choose a charity and an amount to proceed with <strong>eSewa</strong>.</p>
            <hr>

            <form action="payment_process.php" method="post">
                
                <div class="form-group">
                    <label for="charity_id">Select Charity</label>
                    <select id="charity_id" name="charity_id" class="form-control" required>
                        <option value="">-- Choose a Charity --</option>
                        <?php foreach ($charities as $charity): ?>
                            <option value="<?php echo $charity['CharityId']; ?>" 
                                <?php echo ($charity['CharityId'] == $selected_charity_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($charity['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Donation Amount (NPR)</label>
                    <input type="number" id="amount" name="amount" class="form-control" min="1" step="0.01" placeholder="e.g. 500" required>
                </div>
                
                <div class="form-group" style="margin-top: 25px;">
                    <button type="submit" class="btn-esewa">
                        Proceed to eSewa Payment
                    </button>
                </div>

                <div style="text-align: center; margin-top: 15px;">
                    <img src="https://w7.pngtree.com/pngstatic/725/444/png-transparent-esewa-nepal-payment-gateway-logo-indicator-thumbnail.png" alt="eSewa" width="80">
                </div>
            </form>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>