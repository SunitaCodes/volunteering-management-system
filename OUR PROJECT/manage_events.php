<?php
session_start();
require_once "config.php";

// === Security Check: Admin Only ===
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: login.php");
    exit;
}

// Initialize variables for form
$title = $description = $date = $start_time = $end_time = $location = "";
$required_volunteers = 1;
$is_featured = 0; // Default to not featured
$title_err = $date_err = $volunteers_err = $action_message = "";
$is_edit = false;
$edit_id = null;

// --- 1. Handle GET Request for Editing ---
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $sql = "SELECT Title, Description, Date, StartTime, EndTime, RequiredVolunteers, Location, IsFeatured FROM events WHERE EventId = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_bind_result($stmt, $title, $description, $date, $start_time, $end_time, $required_volunteers, $location, $is_featured);
            if (mysqli_stmt_fetch($stmt)) {
                $is_edit = true;
            } else {
                $action_message = "<p style='color: red;'>Event not found for editing.</p>";
                $edit_id = null;
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// --- 2. Handle POST Request (Add or Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_title = trim($_POST["title"]);
    $post_description = trim($_POST["description"]);
    $post_date = trim($_POST["date"]);
    $post_start_time = trim($_POST["start_time"]);
    $post_end_time = trim($_POST["end_time"]);
    $post_required_volunteers = (int) $_POST["required_volunteers"];
    $post_location = trim($_POST["location"]);
    $post_is_featured = isset($_POST["is_featured"]) ? 1 : 0;
    $post_edit_id = isset($_POST["edit_id"]) ? $_POST["edit_id"] : null;

    // Validation
    if (empty($post_title)) {
        $title_err = "Please enter a title.";
    } else {
        $title = $post_title;
    }
    if (empty($post_date)) {
        $date_err = "Please enter a date.";
    } else {
        $date = $post_date;
    }
    if ($post_required_volunteers < 1) {
        $volunteers_err = "Minimum 1 volunteer required.";
    } else {
        $required_volunteers = $post_required_volunteers;
    }

    // Assign cleaned values back to variables for form persistence
    $description = $post_description;
    $start_time = $post_start_time;
    $end_time = $post_end_time;
    $location = $post_location;
    $is_featured = $post_is_featured;

    // Execute INSERT or UPDATE
    if (empty($title_err) && empty($date_err) && empty($volunteers_err)) {

        if ($post_edit_id) {
            $sql = "UPDATE events SET Title = ?, Description = ?, Date = ?, StartTime = ?, EndTime = ?, RequiredVolunteers = ?, Location = ?, IsFeatured = ? WHERE EventId = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssssisii", $title, $description, $date, $start_time, $end_time, $required_volunteers, $location, $is_featured, $post_edit_id);
                if (mysqli_stmt_execute($stmt)) {
                    $action_message = "<p style='color: green;'>Event updated successfully!</p>";
                } else {
                    $action_message = "<p style='color: red;'>ERROR: Could not update event. " . mysqli_error($link) . "</p>";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $sql = "INSERT INTO events (Title, Description, Date, StartTime, EndTime, RequiredVolunteers, Location, IsFeatured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssssisi", $title, $description, $date, $start_time, $end_time, $required_volunteers, $location, $is_featured);
                if (mysqli_stmt_execute($stmt)) {
                    $action_message = "<p style='color: green;'>Event added successfully!</p>";
                    // Clear form after successful insert
                    $title = $description = $date = $start_time = $end_time = $location = "";
                    $required_volunteers = 1;
                    $is_featured = 0;
                } else {
                    $action_message = "<p style='color: red;'>ERROR: Could not add event. " . mysqli_error($link) . "</p>";
                }
                mysqli_stmt_close($stmt);
            }
        }
        // Use session message and redirect to clear POST data
        $_SESSION['action_message'] = $action_message;
        header("location: manage_events.php");
        exit;
    }
}

// --- 3. Handle Delete Request ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Deletion will cascade to volunteer_signups due to foreign key constraint
    $sql_delete = "DELETE FROM events WHERE EventId = ?";
    if ($stmt_delete = mysqli_prepare($link, $sql_delete)) {
        mysqli_stmt_bind_param($stmt_delete, "i", $delete_id);
        if (mysqli_stmt_execute($stmt_delete)) {
            $_SESSION['action_message'] = "<p style='color: green;'>Event successfully deleted (and signups cleared).</p>";
        } else {
            $_SESSION['action_message'] = "<p style='color: red;'>ERROR: Could not delete event. " . mysqli_error($link) . "</p>";
        }
        mysqli_stmt_close($stmt_delete);
        header("location: manage_events.php");
        exit;
    }
}

// Check for and display session message (from redirect)
if (isset($_SESSION['action_message'])) {
    $action_message = $_SESSION['action_message'];
    unset($_SESSION['action_message']);
}

// --- 4. Fetch All Events ---
$sql_fetch = "SELECT EventId, Title, Date, StartTime, EndTime, RequiredVolunteers, Location, IsFeatured FROM events ORDER BY Date DESC";
$events = [];
if ($result = mysqli_query($link, $sql_fetch)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $signup_count = 0;
        $sql_count = "SELECT COUNT(SignupId) AS count FROM volunteer_signups WHERE EventId = ?";
        if ($stmt_count = mysqli_prepare($link, $sql_count)) {
            mysqli_stmt_bind_param($stmt_count, "i", $row['EventId']);
            if (mysqli_stmt_execute($stmt_count)) {
                $count_result = mysqli_stmt_get_result($stmt_count);
                $count_row = mysqli_fetch_assoc($count_result);
                $signup_count = $count_row['count'];
            }
            mysqli_stmt_close($stmt_count);
        }
        $row['CurrentSignups'] = $signup_count;
        $events[] = $row;
    }
    mysqli_free_result($result);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: Manage Events</title>
    <link rel="stylesheet" href="style.css?v=2">
    <style>
        /* Minimal modal fallback in case cached CSS is stale */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; visibility: hidden; transition: opacity .25s ease, visibility .25s ease; z-index: 999; }
        .modal-overlay.open { opacity: 1; visibility: visible; }
        .modal-card { background: #ffffff; color: #0f172a; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); padding: 22px; width: min(640px, 100%); max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 12px; }
        .modal-close { border: none; background: #e2e8f0; color: #0f172a; border-radius: 10px; width: 36px; height: 36px; cursor: pointer; }
    </style>
</head>
<body class="page-content">
    <?php include('header.php'); ?>

    <?php $event_modal_open = $is_edit || !empty($title_err) || !empty($date_err) || !empty($volunteers_err); ?>

    <div class="content-shell">
        <div class="page-header">
            <div>
                <div class="pill">Admin - Events</div>
                <h2 style="margin: 6px 0; color: #0f172a;">Manage Volunteer Events</h2>
            </div>
            <div>
                <button id="open-event-modal" class="btn btn-primary"><?php echo $is_edit ? 'Edit Event' : 'Create Event'; ?></button>
            </div>
        </div>

        <?php if (!empty($action_message)): ?>
            <div class="card" style="margin-bottom: 16px;">
                <?php echo $action_message; ?>
            </div>
        <?php endif; ?>

        <h3 style="margin: 14px 0; color: #0f172a;">All Events (<?php echo count($events); ?>)</h3>
        <?php if (count($events) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title & Status</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th>Volunteers</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event):
                        $is_past = strtotime($event['Date']) < time();
                        $status_class = $is_past ? 'badge gray' : 'badge green';
                        $status_text = $is_past ? 'Past' : 'Upcoming';
                    ?>
                    <tr>
                        <td><?php echo $event['EventId']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($event['Title']); ?></strong><br>
                            <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            <?php if ($event['IsFeatured']): ?>
                                <span class="badge gold">Featured</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo date("M d, Y", strtotime($event['Date'])); ?><br>
                            <?php echo ($event['StartTime'] ? date("g:i A", strtotime($event['StartTime'])) : 'N/A'); ?>
                            —
                            <?php echo ($event['EndTime'] ? date("g:i A", strtotime($event['EndTime'])) : 'N/A'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($event['Location']); ?></td>
                        <td>
                            <?php echo $event['CurrentSignups']; ?> / <?php echo $event['RequiredVolunteers']; ?>
                            <?php if ($event['CurrentSignups'] >= $event['RequiredVolunteers'] && !$is_past): ?>
                                <span class="badge green" style="margin-left:6px;">Full</span>
                            <?php endif; ?>
                        </td>
                        <td class="action-links">
                            <a href="manage_events.php?edit_id=<?php echo $event['EventId']; ?>">Edit</a> |
                            <a href="manage_events.php?delete_id=<?php echo $event['EventId']; ?>" onclick="return confirm('Delete <?php echo addslashes($event['Title']); ?>? This removes all related signups.');" style="color:#ef4444;">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No events have been created yet.</p>
        <?php endif; ?>
    </div>

    <div id="event-modal" class="modal-overlay <?php echo $event_modal_open ? 'open' : ''; ?>">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="margin:0;"><?php echo $is_edit ? 'Edit Event' : 'Create Event'; ?></h3>
                <button type="button" class="modal-close" data-close-event>&times;</button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Event Title*</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                    <span class="error" style="color: #fca5a5;">&nbsp;<?php echo $title_err; ?></span>
                </div>
                
                <div class="form-group" style="display:flex; gap:12px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:180px;">
                        <label>Date*</label>
                        <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required>
                        <span class="error" style="color: #fca5a5;">&nbsp;<?php echo $date_err; ?></span>
                    </div>
                    <div style="flex:1; min-width:140px;">
                        <label>Start Time</label>
                        <input type="time" name="start_time" value="<?php echo htmlspecialchars($start_time); ?>">
                    </div>
                    <div style="flex:1; min-width:140px;">
                        <label>End Time</label>
                        <input type="time" name="end_time" value="<?php echo htmlspecialchars($end_time); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>">
                </div>

                <div class="form-group" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                    <div style="flex:1; min-width:180px;">
                        <label>Required Volunteers*</label>
                        <input type="number" name="required_volunteers" min="1" value="<?php echo htmlspecialchars($required_volunteers); ?>" required>
                        <span class="error" style="color: #fca5a5;">&nbsp;<?php echo $volunteers_err; ?></span>
                    </div>
                    <label style="display:flex; align-items:center; gap:8px; margin-top:20px;">
                        <input type="checkbox" name="is_featured" value="1" <?php echo $is_featured ? 'checked' : ''; ?>>
                        Feature on Home Page
                    </label>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div class="card-actions">
                    <input type="submit" class="btn btn-primary" value="<?php echo $is_edit ? 'Update Event' : 'Create Event'; ?>">
                    <button type="button" class="btn btn-ghost" data-close-event>Close</button>
                    <?php if ($is_edit): ?>
                        <a class="btn btn-ghost danger" href="manage_events.php">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function() {
        const modal = document.getElementById('event-modal');
        const openBtn = document.getElementById('open-event-modal');
        const closeButtons = modal ? modal.querySelectorAll('[data-close-event]') : [];

        function openModal() {
            if (modal) { modal.classList.add('open'); }
        }
        function closeModal() {
            if (modal) { modal.classList.remove('open'); }
            <?php if ($is_edit): ?>
            window.location.href = 'manage_events.php';
            <?php endif; ?>
        }
        openBtn && openBtn.addEventListener('click', function(e) { e.preventDefault(); openModal(); });
        closeButtons.forEach(btn => btn.addEventListener('click', function(e) { e.preventDefault(); closeModal(); }));
        modal && modal.addEventListener('click', function(e) { if (e.target === modal) { closeModal(); } });
        <?php if ($event_modal_open): ?>
        openModal();
        <?php endif; ?>
    })();
    </script>
</body>
</html>
