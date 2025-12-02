<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "volunteer"){
    header("location: login.php");
    exit;
}

$search_term = "";
$charities = [];
$error_message = "";
$search_executed = false;
if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])){
    $search_term = trim($_GET['search']);
    $search_executed = true;
}

$sql = "SELECT CharityId, Name, Address, Description FROM charities";

if ($search_executed && !empty($search_term)) {
    $sql .= " WHERE Name LIKE ? OR Address LIKE ? OR Description LIKE ?";
    $sql .= " ORDER BY Name ASC";
    if ($stmt = mysqli_prepare($link, $sql)) {
        $param = "%" . $search_term . "%";
        mysqli_stmt_bind_param($stmt, "sss", $param, $param, $param);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $charities[] = $row;
            }
        } else {
            $error_message = "ERROR: Could not execute search query. " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $sql .= " ORDER BY Name ASC";
    if ($result = mysqli_query($link, $sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $charities[] = $row;
        }
        mysqli_free_result($result);
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search and Donate</title>
    <link rel="stylesheet" href="style.css?v=2">
    <style>
        /* Keep the original card layout, just add spacing via content-shell */
        .charity-card { background:#ffffff; color:#0f172a; border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .charity-card h3 { color: #0f172a; margin-top:0; }
        .charity-card p { color:#1f2937; }
        form { margin-bottom: 20px; }
        .donate-btn { 
            background-color: #4CAF50; 
            color: white; 
            padding: 8px 12px; 
            border: none; 
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }
        .donate-btn:hover { background-color: #45a049; }
    </style>
</head>
<body class="page-content">
    <?php include('header.php'); ?>
    <div class="content-shell">
        <h2 style="margin: 10px 0 16px; color:#0f172a;">Search and Donate to Charities</h2>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
            <input type="text" name="search" placeholder="Search by name, address, or keyword..." 
                   value="<?php echo htmlspecialchars($search_term); ?>" style="padding: 8px; width: 300px;">
            <input type="submit" value="Search" style="padding: 8px 15px;">
        </form>
        
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if (count($charities) > 0): ?>
            <?php foreach ($charities as $charity): ?>
                <div class="charity-card">
                    <h3><?php echo htmlspecialchars($charity['Name']); ?></h3>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($charity['Address']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($charity['Description']); ?></p>
                    <p>
                        <a href="donate_action.php?charity_id=<?php echo $charity['CharityId']; ?>" class="donate-btn">Donate Now</a>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php elseif ($search_executed): ?>
            <p>No charities found matching your search criteria.</p>
        <?php else: ?>
             <p>No charities are currently registered in the system.</p>
        <?php endif; ?>
    </div>
</body>
</html>
