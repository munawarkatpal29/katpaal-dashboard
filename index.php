<?php
include 'config.php';

// Authentication check
if (!isLoggedIn()) {
    redirectToLogin();
}

$user_id = getCurrentUserId();
$current_dashboard_id = $_SESSION['current_dashboard'] ?? null;

// Get all dashboards for current user
$stmt = $pdo->prepare("SELECT * FROM dashboards WHERE user_id = ? ORDER BY id");
$stmt->execute([$user_id]);
$dashboards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get shortcuts for current dashboard
$shortcuts = [];
$current_dashboard = ['name' => 'No Dashboard'];

if ($current_dashboard_id) {
    $stmt = $pdo->prepare("SELECT * FROM shortcuts WHERE dashboard_id = ? ORDER BY sort_order");
    $stmt->execute([$current_dashboard_id]);
    $shortcuts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT name FROM dashboards WHERE id = ? AND user_id = ?");
    $stmt->execute([$current_dashboard_id, $user_id]);
    $current_dashboard = $stmt->fetch(PDO::FETCH_ASSOC) ?? ['name' => 'No Dashboard'];
}

// Handle dashboard switching
if (isset($_GET['dashboard'])) {
    $new_dashboard_id = (int)$_GET['dashboard'];
    
    // Verify the dashboard belongs to current user
    $stmt = $pdo->prepare("SELECT id FROM dashboards WHERE id = ? AND user_id = ?");
    $stmt->execute([$new_dashboard_id, $user_id]);
    
    if ($stmt->fetch()) {
        $_SESSION['current_dashboard'] = $new_dashboard_id;
        header('Location: index.php');
        exit();
    }
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

function getFavicon($url) {
    try { 
        $u = parse_url($url);
        if (isset($u['host'])) {
            return "https://www.google.com/s2/favicons?domain=" . $u['host'] . "&sz=64";
        }
    } catch(Exception $e) { 
        return "";
    }
    return "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Katpaal Links Dashboard</title>
<style>
/* Previous CSS styles remain the same, just adding logout button style */
.logout-btn { background: #ff4444 !important; color: white !important; margin-left: auto; }
.logout-btn:hover { background: #cc0000 !important; }
.user-info { display: flex; align-items: center; gap: 10px; margin-top: 10px; justify-content: center; color: white; }
</style>
</head>
<body>

<header>
    <h1>Munawar Nazir Katpaal Dashboard Links</h1>
    <p>Personal Link Launcher</p>
    <div class="user-info">
        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        <a href="?logout=1" style="color: white; margin-left: 15px; text-decoration: none; background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 5px;">Logout</a>
    </div>
    <div class="buttons">
        <button onclick="showModal('addDashboard')">+ Add Dashboard</button>
        <button onclick="showModal('renameDashboard')">+ Rename Dashboard</button>
        <button onclick="showModal('deleteDashboard')">+ Delete Dashboard</button> 
        <button onclick="showModal('addShortcut')">+ Add Shortcut</button>
        <button onclick="showModal('renameShortcut')">+ Rename Shortcut</button>
        <button onclick="showModal('deleteShortcut')">+ Delete Shortcut</button>
    </div>
</header>

<!-- Rest of the HTML remains the same as previous version -->
<nav id="navBar">
    <?php foreach ($dashboards as $dashboard): ?>
        <a href="?dashboard=<?php echo $dashboard['id']; ?>" 
           class="<?php echo $dashboard['id'] == $current_dashboard_id ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($dashboard['name']); ?>
        </a>
    <?php endforeach; ?>
</nav>

<div class="page-title" id="pageTitle"><?php echo htmlspecialchars($current_dashboard['name']); ?></div>

<section class="dashboard" id="dashboard">
    <?php if (empty($shortcuts)): ?>
        <div class="empty-state">
            <h3>No shortcuts yet</h3>
            <p>Click "Add Shortcut" to create your first shortcut</p>
        </div>
    <?php else: ?>
        <?php foreach ($shortcuts as $shortcut): ?>
            <div class="card" draggable="true" data-id="<?php echo $shortcut['id']; ?>">
                <img src="<?php echo getFavicon($shortcut['url']); ?>" alt="favicon">
                <h3><?php echo htmlspecialchars($shortcut['name']); ?></h3>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<footer>
    <p>© 2025 Munawar Nazir Katpaal Dashboard Links — All Rights Reserved</p>
</footer>

<!-- All modals remain the same as previous version -->
<!-- JavaScript remains the same but AJAX calls will now include user authentication -->

<script>
// AJAX functions with user authentication
function addDashboard() {
    const name = document.getElementById('newDashboardName').value.trim();
    if(name) { 
        fetch('ajax.php?action=add_dashboard', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'name=' + encodeURIComponent(name)
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                showToast(data.message || "Error adding dashboard");
            }
        });
        closeModal('addDashboardModal');
    } else {
        showToast("Please enter a dashboard name");
    }
}

// Other AJAX functions remain the same...
</script>
</body>
</html>