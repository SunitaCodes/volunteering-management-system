<?php
session_start();
require_once "config.php";

// === Security Check: Admin Only ===
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: login.php");
    exit;
}

$charities = [];
$name = $address = $description = "";
$name_err = $address_err = $description_err = $action_message = "";
$is_edit = false; // Flag for edit mode
$edit_id = null;

// --- 1. Handle GET Request for Editing ---
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $sql = "SELECT Name, Address, Description FROM charities WHERE CharityId = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_bind_result($stmt, $name, $address, $description);
            if (mysqli_stmt_fetch($stmt)) {
                $is_edit = true;
            } else {
                $action_message = "<p style='color: red;'>Charity not found for editing.</p>";
                $edit_id = null;
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// --- 2. Handle POST Request (Add or Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_name = trim($_POST["name"]);
    $post_address = trim($_POST["address"]);
    $post_description = trim($_POST["description"]);
    $post_edit_id = isset($_POST["edit_id"]) ? $_POST["edit_id"] : null;

    // Validation
    if (empty($post_name)) { $name_err = "Please enter a charity name."; } else { $name = $post_name; }
    if (empty($post_address)) { $address_err = "Please enter an address."; } else { $address = $post_address; }
    if (empty($post_description)) { $description_err = "Please enter a description."; } else { $description = $post_description; }

    // Check for duplicate name
    $sql_check = "SELECT CharityId FROM charities WHERE Name = ?";
    if ($post_edit_id) { $sql_check .= " AND CharityId != ?"; }

    if ($stmt_check = mysqli_prepare($link, $sql_check)) {
        if ($post_edit_id) {
            mysqli_stmt_bind_param($stmt_check, "si", $post_name, $post_edit_id);
        } else {
            mysqli_stmt_bind_param($stmt_check, "s", $post_name);
        }

        if (mysqli_stmt_execute($stmt_check)) {
            mysqli_stmt_store_result($stmt_check);
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $name_err = "This charity name already exists.";
            }
        }
        mysqli_stmt_close($stmt_check);
    }

    // Execute INSERT or UPDATE
    if (empty($name_err) && empty($address_err) && empty($description_err)) {
        if ($post_edit_id) {
            $sql = "UPDATE charities SET Name = ?, Address = ?, Description = ? WHERE CharityId = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssi", $name, $address, $description, $post_edit_id);
                if (mysqli_stmt_execute($stmt)) {
                    $action_message = "<p style='color: green;'>Charity updated successfully!</p>";
                    $name = $address = $description = "";
                    $is_edit = false;
                    $edit_id = null;
                } else {
                    $action_message = "<p style='color: red;'>ERROR: Could not update charity. " . mysqli_error($link) . "</p>";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $sql = "INSERT INTO charities (Name, Address, Description) VALUES (?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sss", $name, $address, $description);
                if (mysqli_stmt_execute($stmt)) {
                    $action_message = "<p style='color: green;'>Charity added successfully!</p>";
                    $name = $address = $description = "";
                } else {
                    $action_message = "<p style='color: red;'>ERROR: Could not add charity. " . mysqli_error($link) . "</p>";
                }
                mysqli_stmt_close($stmt);
            }
        }
        $_SESSION['action_message'] = $action_message;
        header("location: manage_charities.php");
        exit;
    }
}

// --- 3. Handle Delete Request ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    $sql_delete = "DELETE FROM charities WHERE CharityId = ?";
    if ($stmt_delete = mysqli_prepare($link, $sql_delete)) {
        mysqli_stmt_bind_param($stmt_delete, "i", $delete_id);
        if (mysqli_stmt_execute($stmt_delete)) {
            $_SESSION['action_message'] = "<p style='color: green;'>Charity successfully deleted.</p>";
        } else {
            if (mysqli_errno($link) == 1451) {
                $_SESSION['action_message'] = "<p style='color: orange;'>Cannot delete charity: It has recorded donations. Delete the donations first.</p>";
            } else {
                $_SESSION['action_message'] = "<p style='color: red;'>ERROR: Could not delete charity. " . mysqli_error($link) . "</p>";
            }
        }
        mysqli_stmt_close($stmt_delete);
        header("location: manage_charities.php");
        exit;
    }
}

// Check for and display session message (from redirect)
if (isset($_SESSION['action_message'])) {
    $action_message = $_SESSION['action_message'];
    unset($_SESSION['action_message']);
}

// --- 4. Fetch All Charities ---
$sql_fetch = "SELECT CharityId, Name, Address, Description FROM charities ORDER BY Name ASC";
if ($result = mysqli_query($link, $sql_fetch)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $charities[] = $row;
    }
    mysqli_free_result($result);
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: Manage Charities</title>
    <link rel="stylesheet" href="style.css?v=2">
    <style>
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; visibility: hidden; transition: opacity .25s ease, visibility .25s ease; z-index: 999; }
        .modal-overlay.open { opacity: 1; visibility: visible; }
        .modal-card { background: #ffffff; color: #0f172a; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); padding: 22px; width: min(640px, 100%); max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 12px; }
        .modal-close { border: none; background: #e2e8f0; color: #0f172a; border-radius: 10px; width: 36px; height: 36px; cursor: pointer; }
    </style>
</head>
<body class="page-content">
    <?php include('header.php'); ?>

    <?php $charity_modal_open = $is_edit || !empty($name_err) || !empty($address_err) || !empty($description_err); ?>

    <div class="content-shell">
        <div class="page-header">
            <div>
                <div class="pill">Admin - Charities</div>
                <h2 style="margin: 6px 0; color: #0f172a;">Manage Community Charities</h2>
            </div>
            <div>
                <button id="open-charity-modal" class="btn btn-primary"><?php echo $is_edit ? 'Edit Charity' : 'Add Charity'; ?></button>
            </div>
        </div>

        <?php if (!empty($action_message)): ?>
            <div class="card" style="margin-bottom: 16px;">
                <?php echo $action_message; ?>
            </div>
        <?php endif; ?>

        <h3 style="margin: 14px 0; color: #0f172a;">Current Charities (<?php echo count($charities); ?>)</h3>
        <?php if (count($charities) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($charities as $charity): ?>
                    <tr>
                        <td><?php echo $charity['CharityId']; ?></td>
                        <td><strong><?php echo htmlspecialchars($charity['Name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($charity['Address']); ?></td>
                        <td><?php echo htmlspecialchars(substr($charity['Description'], 0, 140)) . (strlen($charity['Description']) > 140 ? '...' : ''); ?></td>
                        <td class="action-links">
                            <a href="manage_charities.php?edit_id=<?php echo $charity['CharityId']; ?>">Edit</a> |
                            <a href="manage_charities.php?delete_id=<?php echo $charity['CharityId']; ?>" onclick="return confirm('Delete <?php echo addslashes($charity['Name']); ?>? This may fail if donations exist.');" style="color:#ef4444;">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No charities have been registered yet.</p>
        <?php endif; ?>
    </div>

    <div id="charity-modal" class="modal-overlay <?php echo $charity_modal_open ? 'open' : ''; ?>">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="margin:0;"><?php echo $is_edit ? 'Edit Charity' : 'Add New Charity'; ?></h3>
                <button type="button" class="modal-close" data-close-charity>&times;</button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Name*</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    <span class="error" style="color: #fca5a5;">&nbsp;<?php echo $name_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Address*</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
                    <span class="error" style="color: #fca5a5;">&nbsp;<?php echo $address_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Description*</label>
                    <textarea name="description" required><?php echo htmlspecialchars($description); ?></textarea>
                    <span class="error" style="color: #fca5a5;">&nbsp;<?php echo $description_err; ?></span>
                </div>
                
                <div class="card-actions">
                    <input type="submit" class="btn btn-primary" value="<?php echo $is_edit ? 'Update Charity' : 'Add Charity'; ?>">
                    <button type="button" class="btn btn-ghost" data-close-charity>Close</button>
                    <?php if ($is_edit): ?>
                        <a class="btn btn-ghost danger" href="manage_charities.php">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function() {
        const modal = document.getElementById('charity-modal');
        const openBtn = document.getElementById('open-charity-modal');
        const closeButtons = modal ? modal.querySelectorAll('[data-close-charity]') : [];

        function openModal() { if (modal) { modal.classList.add('open'); } }
        function closeModal() {
            if (modal) { modal.classList.remove('open'); }
            <?php if ($is_edit): ?>
            window.location.href = 'manage_charities.php';
            <?php endif; ?>
        }

        openBtn && openBtn.addEventListener('click', function(e) { e.preventDefault(); openModal(); });
        closeButtons.forEach(btn => btn.addEventListener('click', function(e) { e.preventDefault(); closeModal(); }));
        modal && modal.addEventListener('click', function(e) { if (e.target === modal) { closeModal(); } });
        <?php if ($charity_modal_open): ?>
        openModal();
        <?php endif; ?>
    })();
    </script>
</body>
</html>
