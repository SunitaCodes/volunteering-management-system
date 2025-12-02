<?php
session_start();
require_once "config.php";

// Check if the user is logged in as a volunteer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "volunteer"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$charity_id = $amount = "";
$charity_name = "Select Charity";
$amount_err = $charity_err = $general_err = "";

// --- 1. Fetch Charities for Dropdown ---
$charities = [];
$sql_charities = "SELECT CharityId, Name FROM charities ORDER BY Name ASC";
if ($result = mysqli_query($link, $sql_charities)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $charities[] = $row;
    }
    mysqli_free_result($result);
} else {
    $general_err = "Error fetching charities: " . mysqli_error($link);
}

// Pre-select a charity if ID is passed in the URL (e.g., from Popular Charities list)
if(isset($_GET['charity_id']) && is_numeric($_GET['charity_id'])){
    $charity_id = (int)$_GET['charity_id'];
    foreach ($charities as $c) {
        if ($c['CharityId'] == $charity_id) {
            $charity_name = $c['Name'];
            break;
        }
    }
}


// --- 2. Handle POST Submission ---
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate Charity
    $charity_id = trim($_POST["charity_id"]);
    if (empty($charity_id) || !is_numeric($charity_id)) {
        $charity_err = "Please select a valid charity.";
    }

    // Validate Amount
    $amount = trim($_POST["amount"]);
    if(empty($amount) || !is_numeric($amount) || $amount <= 0){
        $amount_err = "Please enter a valid donation amount greater than zero.";
    } else {
        $amount = round($amount, 2); // Ensure two decimal places
    }

    // Process donation if no errors
    if(empty($charity_err) && empty($amount_err)){
        $sql = "INSERT INTO donations (UserId, CharityId, Amount, DonationDate) VALUES (?, ?, ?, NOW())";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "iid", $user_id, $charity_id, $amount);
            
            if(mysqli_stmt_execute($stmt)){
                // Success! Redirect to my donations page or a success message
                $_SESSION['action_message'] = "<div class='alert success-alert'>✅ Thank you! Your donation of " . number_format($amount, 2) . " has been recorded.</div>";
                header("location: my_donations.php");
                exit;
            } else{
                $general_err = "ERROR: Could not execute donation. " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Make a Donation</title>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="donation-wrapper">
        <h2>💖 Make a Donation</h2>
        
        <?php if (!empty($general_err)): ?>
            <div class="alert error-alert"><?php echo $general_err; ?></div>
        <?php endif; ?>

        <div class="card donation-form-card">
            <p>Your contribution helps us support vital causes in the community.</p>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                
                <div class="form-group">
                    <label for="charity_id">Select Charity</label>
                    <select id="charity_id" name="charity_id" class="form-control" required>
                        <option value="">-- Choose a Charity --</option>
                        <?php foreach ($charities as $charity): ?>
                            <option value="<?php echo $charity['CharityId']; ?>" 
                                <?php echo ($charity['CharityId'] == $charity_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($charity['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error"><?php echo $charity_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="amount">Donation Amount</label>
                    <input type="number" id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>" min="1" step="0.01" placeholder="e.g., 500.00" required>
                    <span class="error"><?php echo $amount_err; ?></span>
                </div>
                
                <div class="form-group">
                    <input type="submit" value="Complete Donation" class="btn-primary">
                </div>
            </form>
        </div>
        </div>
    
    </div>
</body>
</html>