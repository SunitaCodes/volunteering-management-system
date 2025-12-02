<?php
// Note: This file assumes session_start() has been called in the calling PHP script.
// If your included files don't call it, you should uncomment the line below:
// if (session_status() == PHP_SESSION_NONE) { session_start(); }

$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$is_admin = $is_logged_in && $_SESSION["role"] === "admin";
$is_volunteer = $is_logged_in && $_SESSION["role"] === "volunteer";
$username = $is_logged_in ? htmlspecialchars($_SESSION["username"]) : "Guest";
$current_page = basename($_SERVER["PHP_SELF"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VMS Header</title>
    <style>
        .header-nav {
            background-color: #343a40; /* Dark background */
            padding: 15px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .logo {
            font-size: 1.5em;
            font-weight: bold;
            color: #007bff; /* Highlight color */
            text-decoration: none;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 1.0em;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .nav-links a.active {
            background-color: #495057;
            color: #ffc107;
            font-weight: 600;
        }
        .nav-links a:hover {
            background-color: #495057;
        }
        .user-info {
            color: #ccc;
            font-size: 0.9em;
        }
        .user-info span {
            font-weight: bold;
            color: #fff;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
    </style>
   <?php if (file_exists('style.css')): ?>
        <link rel="stylesheet" href="style.css">
    <?php endif; ?>
</head>
<body>

    <header class="header-nav">
        <a href="index.php" class="logo">VMS Portal</a>
        
        <nav class="nav-links">
            <a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a>
            
            <?php if ($is_logged_in): ?>
                
                <?php if ($is_volunteer): ?>
                    <a href="view_events.php" class="<?php echo $current_page === 'view_events.php' ? 'active' : ''; ?>">Events</a>                  
                    <a href="my_signups.php" class="<?php echo $current_page === 'my_signups.php' ? 'active' : ''; ?>">My Signups</a>
                    <a href="search_donations.php" class="<?php echo $current_page === 'search_donations.php' ? 'active' : ''; ?>">Donate</a>
                    <a href="my_donations.php" class="<?php echo $current_page === 'my_donations.php' ? 'active' : ''; ?>">My Donations</a>
                <?php endif; ?>

                <?php if ($is_admin): ?>
                    <span style="color: #ffc107; margin: 0 10px;">|</span>
                    <a href="admin_dashboard.php" class="<?php echo $current_page === 'admin_dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="manage_events.php" class="<?php echo $current_page === 'manage_events.php' ? 'active' : ''; ?>">Manage Events</a>
                    <a href="manage_charities.php" class="<?php echo $current_page === 'manage_charities.php' ? 'active' : ''; ?>">Manage Charities</a>
                    <a href="manage_volunteers.php" class="<?php echo $current_page === 'manage_volunteers.php' ? 'active' : ''; ?>">Manage Users</a>
                    <span style="color: #ffc107; margin: 0 10px;">|</span>
                <?php endif; ?>

                <a href="logout.php" style="color: #dc3545;">Logout</a>

            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
        
        <div class="user-info">
            <?php if ($is_logged_in): ?>
                Logged in as <span style="text-transform: capitalize;"><?php echo $username; ?> (<?php echo $_SESSION['role']; ?>)</span>
            <?php else: ?>
                Welcome, Guest
            <?php endif; ?>
        </div>
    </header>
