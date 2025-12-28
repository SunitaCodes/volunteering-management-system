<?php
session_start();
require_once "config.php";

// 1. Check if eSewa sent data back
if (isset($_GET['data'])) {
    $encoded_data = $_GET['data'];
    $decoded_json = base64_decode($encoded_data);
    $data = json_decode($decoded_json, true);

    // 2. Verify status is 'COMPLETE'
    if ($data['status'] === "COMPLETE") {
        
        // Ensure we have the User ID and Charity ID from the session
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
        $charity_id = isset($_SESSION['temp_donation']['charity_id']) ? $_SESSION['temp_donation']['charity_id'] : null;
        
        $amount = $data['total_amount']; 
        $transaction_code = $data['transaction_code'];

        // Only proceed if we have both IDs
        if ($user_id && $charity_id) {
            // 3. Insert into 'donations' table
            $sql = "INSERT INTO donations (UserId, CharityId, Amount, DonationDate) VALUES (?, ?, ?, NOW())";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "iid", $user_id, $charity_id, $amount);
                
                if(mysqli_stmt_execute($stmt)){
                    echo "<div style='text-align:center; padding:50px;'>";
                    echo "<h1 style='color:green;'>✔️ Payment Successful!</h1>";
                    echo "<p>Your donation has been recorded in our database.</p>";
                    echo "<p>Transaction ID: " . htmlspecialchars($transaction_code) . "</p>";
                    echo "<a href='index.php' style='text-decoration:none; color:blue;'>Return to Dashboard</a>";
                    echo "</div>";
                    
                    // Optional: Clear the temporary session data
                    unset($_SESSION['temp_donation']);
                } else {
                    echo "Database Error: " . mysqli_error($link);
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            echo "Error: Missing User or Charity information. Please log in again.";
        }
    } else {
        echo "Payment was not completed. Status: " . $data['status'];
    }
} else {
    echo "No payment data received.";
}
?>