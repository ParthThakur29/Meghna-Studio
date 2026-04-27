<?php
session_start();
require_once 'db_config.php';

// Set your custom admin password here
$admin_password = "meghnastudio"; 

if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Incorrect password!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Meghna Studio</title>
  <link rel="stylesheet" href="base.css">
  <style>
    body { padding: 40px; background: var(--dark); color: var(--cream); }
    .admin-container { max-width: 1200px; margin: 0 auto; }
    h1 { margin-bottom: 20px; font-family: var(--serif); color: var(--gold); }
    
    .login-box { background: var(--dark2); padding: 40px; border-radius: 8px; max-width: 400px; margin: 100px auto; border: 1px solid rgba(200,162,106,0.2); }
    .login-box input { width: 100%; padding: 15px; margin: 10px 0 20px; background: var(--dark); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 4px; font-family: var(--sans); }
    .login-box input:focus { outline: none; border-color: var(--gold); }
    
    .admin-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: var(--dark2); border-radius: 8px; overflow: hidden; font-size: 14px; }
    .admin-table th, .admin-table td { padding: 15px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .admin-table th { background: rgba(0,0,0,0.3); color: var(--gold); font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; }
    .admin-table tr:hover { background: rgba(255,255,255,0.03); }
    
    .error { color: #ff6b6b; font-size: 14px; margin-bottom: 10px; }
    .logout { float: right; color: var(--gold); font-size: 14px; margin-top: 10px; letter-spacing: 0.1em; text-transform: uppercase; }
    .logout:hover { color: var(--gold-lt); }
    
    .empty-state { padding: 40px; text-align: center; background: var(--dark2); border-radius: 8px; color: rgba(255,255,255,0.5); }
  </style>
</head>
<body>
<div class="admin-container">
    <?php if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true): ?>
        <!-- LOGIN SCREEN -->
        <div class="login-box">
            <h1>Admin Panel</h1>
            <p style="color: rgba(255,255,255,0.5); margin-bottom: 20px; font-size: 14px;">Enter password to view appointments</p>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="btn btn-solid" style="width: 100%;">Login to Dashboard</button>
            </form>
        </div>
    <?php else: ?>
        <!-- DASHBOARD SCREEN -->
        <a href="?logout=1" class="logout">Logout</a>
        <h1>Enquiries Dashboard</h1>
        
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM enquiries ORDER BY created_at DESC");
            $enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($enquiries) > 0) {
                echo "<table class='admin-table'>
                        <tr>
                            <th>Date Applied</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Event Type</th>
                            <th>Event Date</th>
                            <th>Location</th>
                            <th>Message</th>
                        </tr>";
                foreach ($enquiries as $row) {
                    echo "<tr>
                            <td style='color: rgba(255,255,255,0.5);'>" . date('d M Y, h:i A', strtotime($row['created_at'])) . "</td>
                            <td style='color: var(--gold); font-weight: 600;'>" . htmlspecialchars($row['name']) . "</td>
                            <td>" . htmlspecialchars($row['phone']) . "</td>
                            <td>" . htmlspecialchars($row['email']) . "</td>
                            <td>" . htmlspecialchars($row['event_type']) . "</td>
                            <td>" . htmlspecialchars($row['event_date']) . "</td>
                            <td>" . htmlspecialchars($row['location']) . "</td>
                            <td style='max-width: 300px; color: rgba(255,255,255,0.6);'>" . htmlspecialchars($row['message']) . "</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='empty-state'>No enquiries found yet. When someone submits the form, it will appear here.</div>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Error fetching data: " . $e->getMessage() . "</p>";
        }
        ?>
    <?php endif; ?>
</div>
</body>
</html>
