<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if logged in user is admin
$user_stmt = $conn->prepare("SELECT role, name FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();

if (!$user_data || ($user_data['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit();
}

$admin_name = $user_data['name'] ?? 'Admin';
$view = $_GET['view'] ?? 'dashboard';
$search = trim($_GET['search'] ?? '');

$alerts = $_SESSION['alerts'] ?? [];
unset($_SESSION['alerts']);

// Action logic for adding a new match/fixture
if (isset($_POST['add_schedule_btn'])) {
    $title      = trim($_POST['title']);
    $sport_type = trim($_POST['sport_type']);
    $event_type = trim($_POST['event_type']);
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);
    $venue      = trim($_POST['venue']);

    $stmt = $conn->prepare("INSERT INTO matches_schedules (title, sport_type, event_type, event_date, event_time, venue, status) VALUES (?, ?, ?, ?, ?, ?, 'UPCOMING')");
    $stmt->bind_param("ssssss", $title, $sport_type, $event_type, $event_date, $event_time, $venue);
    
    if ($stmt->execute()) {
        $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'New match schedule added successfully!'];
    } else {
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Failed to add schedule.'];
    }

    header('Location: admin.php?view=schedules');
    exit();
}

// Action logic for Club Membership Approve/Reject
if (isset($_GET['action']) && isset($_GET['id']) && !isset($_GET['type'])) {
    $request_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $update_stmt = $conn->prepare("UPDATE user_clubs SET status = 'approved' WHERE id = ?");
        $update_stmt->bind_param("i", $request_id);
        $update_stmt->execute();
        $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Club membership approved successfully!'];
    } elseif ($action === 'reject') {
        $update_stmt = $conn->prepare("UPDATE user_clubs SET status = 'rejected' WHERE id = ?");
        $update_stmt->bind_param("i", $request_id);
        $update_stmt->execute();
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Club membership request rejected!'];
    }

    $redirect_url = 'admin.php?view=approvals' . ($search !== '' ? '&search=' . urlencode($search) : '');
    header('Location: ' . $redirect_url);
    exit();
}

// Action logic for Facility Booking Approve/Reject
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['type']) && $_GET['type'] === 'booking') {
    $booking_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $update_stmt = $conn->prepare("UPDATE facility_bookings SET status = 'approved' WHERE id = ?");
        $update_stmt->bind_param("i", $booking_id);
        $update_stmt->execute();
        $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Facility booking request approved!'];
    } elseif ($action === 'reject') {
        $update_stmt = $conn->prepare("UPDATE facility_bookings SET status = 'rejected' WHERE id = ?");
        $update_stmt->bind_param("i", $booking_id);
        $update_stmt->execute();
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Facility booking request rejected!'];
    }

    $redirect_url = 'admin.php?view=bookings' . ($search !== '' ? '&search=' . urlencode($search) : '');
    header('Location: ' . $redirect_url);
    exit();
}

// Fetch pending club join requests with Search
$search_param = '%' . $search . '%';

$requests_sql = "
    SELECT uc.id, u.name, u.email, uc.club_name, uc.joined_at 
    FROM user_clubs uc 
    JOIN users u ON uc.user_id = u.id 
    WHERE uc.status = 'pending'
";
if ($search !== '') {
    $requests_sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR uc.club_name LIKE ?)";
    $req_stmt = $conn->prepare($requests_sql . " ORDER BY uc.joined_at DESC");
    $req_stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $req_stmt->execute();
    $requests_result = $req_stmt->get_result();
} else {
    $requests_result = $conn->query($requests_sql . " ORDER BY uc.joined_at DESC");
}

// Fetch active approved clubs with Search
$clubs_sql = "
    SELECT DISTINCT club_name, COUNT(user_id) as member_count 
    FROM user_clubs 
    WHERE status = 'approved'
";
if ($search !== '') {
    $clubs_sql .= " AND club_name LIKE ?";
    $club_stmt = $conn->prepare($clubs_sql . " GROUP BY club_name");
    $club_stmt->bind_param("s", $search_param);
    $club_stmt->execute();
    $clubs_result = $club_stmt->get_result();
} else {
    $clubs_result = $conn->query($clubs_sql . " GROUP BY club_name");
}

// Fetch pending facility bookings with Search
$bookings_sql = "
    SELECT fb.id, u.name, u.email, fb.facility_name, fb.booking_date, fb.time_slot, fb.status 
    FROM facility_bookings fb 
    JOIN users u ON fb.user_id = u.id 
    WHERE fb.status = 'pending'
";
if ($search !== '') {
    $bookings_sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR fb.facility_name LIKE ?)";
    $book_stmt = $conn->prepare($bookings_sql . " ORDER BY fb.booking_date ASC");
    $book_stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $book_stmt->execute();
    $bookings_result = $book_stmt->get_result();
} else {
    $bookings_result = $conn->query($bookings_sql . " ORDER BY fb.booking_date ASC");
}

// Fetch existing matches and schedules
$schedules_list = $conn->query("SELECT * FROM matches_schedules ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Panel - NSBM SportsHub</title>
    <link rel="stylesheet" href="style.css?v=9.7">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body class="admin-page-body">

    <!-- COMPACT MATCHED ADMIN HEADER -->
    <header class="admin-header">
        <a href="index.php" class="logo">
            <img src="logo2.png" alt="Logo">
        </a>
        <div class="user-auth">
            <div class="profile-box">
                <div class="avatar-circle"><?= strtoupper($_SESSION['name'][0] ?? 'A') ?></div>
                <div class="dropdown">
                    <a href="profile.php">My account</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- ACTION SUCCESS/ERROR ALERT -->
    <?php if (!empty($alerts)): ?>
    <div class="alert-box">
        <?php foreach($alerts as $alert): ?>
        <div class="alert <?= $alert['type'] ?>">
            <i class='bx <?= $alert['type'] === 'success' ? 'bxs-check-circle' : 'bxs-x-circle'; ?>'></i>
            <span><?= htmlspecialchars($alert['message']); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <section class="admin-hero-section">
        <?php if ($view === 'dashboard'): ?>
            <!-- MAIN DASHBOARD -->
            <h1 class="admin-main-title">Admin Control Panel</h1>

            <div class="canva-card-container">
                <a href="admin.php?view=clubs" class="canva-card">
                    <h2>Active<br>clubs</h2>
                </a>

                <a href="admin.php?view=approvals" class="canva-card">
                    <h2>Student<br>Membership<br>Approvals</h2>
                </a>

                <a href="admin.php?view=bookings" class="canva-card">
                    <h2>Facility Booking<br>Request</h2>
                </a>

                <a href="admin.php?view=schedules" class="canva-card">
                    <h2>Manage<br>Schedules &<br>Matches</h2>
                </a>
            </div>

        <?php elseif ($view === 'approvals'): ?>
            <!-- SUB PAGE 1: STUDENT MEMBERSHIP APPROVALS -->
            <div class="admin-subcontainer">
                <div class="view-header">
                    <h2>Student Membership Approvals</h2>
                    <a href="admin.php" class="btn-back"><i class='bx bx-arrow-back'></i> Back</a>
                </div>

                <!-- SEARCH BAR -->
                <form action="admin.php" method="GET" class="admin-search-form">
                    <input type="hidden" name="view" value="approvals">
                    <div class="search-input-group">
                        <i class='bx bx-search'></i>
                        <input type="text" name="search" placeholder="Search student name, email, or club..." value="<?= htmlspecialchars($search) ?>">
                        <?php if ($search !== ''): ?>
                            <a href="admin.php?view=approvals" class="clear-search"><i class='bx bx-x'></i></a>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-search">Search</button>
                </form>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Club Name</th>
                                <th>Requested Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests_result && $requests_result->num_rows > 0): ?>
                                <?php while ($row = $requests_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['club_name']) ?></td>
                                        <td><?= date('M d, Y', strtotime($row['joined_at'])) ?></td>
                                        <td>
                                            <a href="admin.php?action=approve&id=<?= $row['id'] ?>&search=<?= urlencode($search) ?>" class="btn-action approve"><i class='bx bx-check'></i> Accept</a>
                                            <a href="admin.php?action=reject&id=<?= $row['id'] ?>&search=<?= urlencode($search) ?>" class="btn-action reject"><i class='bx bx-x'></i> Reject</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-data">No pending join requests found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($view === 'clubs'): ?>
            <!-- SUB PAGE 2: ACTIVE CLUBS -->
            <div class="admin-subcontainer">
                <div class="view-header">
                    <h2>Active Sports Clubs</h2>
                    <a href="admin.php" class="btn-back"><i class='bx bx-arrow-back'></i> Back</a>
                </div>

                <!-- SEARCH BAR -->
                <form action="admin.php" method="GET" class="admin-search-form">
                    <input type="hidden" name="view" value="clubs">
                    <div class="search-input-group">
                        <i class='bx bx-search'></i>
                        <input type="text" name="search" placeholder="Search club name..." value="<?= htmlspecialchars($search) ?>">
                        <?php if ($search !== ''): ?>
                            <a href="admin.php?view=clubs" class="clear-search"><i class='bx bx-x'></i></a>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-search">Search</button>
                </form>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Club Name</th>
                                <th>Active Approved Members</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($clubs_result && $clubs_result->num_rows > 0): ?>
                                <?php while ($row = $clubs_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['club_name']) ?></td>
                                        <td><?= $row['member_count'] ?> Members</td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="no-data">No active clubs found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($view === 'bookings'): ?>
            <!-- SUB PAGE 3: FACILITY BOOKING REQUESTS -->
            <div class="admin-subcontainer">
                <div class="view-header">
                    <h2>Facility Booking Requests</h2>
                    <a href="admin.php" class="btn-back"><i class='bx bx-arrow-back'></i> Back</a>
                </div>

                <!-- SEARCH BAR -->
                <form action="admin.php" method="GET" class="admin-search-form">
                    <input type="hidden" name="view" value="bookings">
                    <div class="search-input-group">
                        <i class='bx bx-search'></i>
                        <input type="text" name="search" placeholder="Search student name, email, or facility..." value="<?= htmlspecialchars($search) ?>">
                        <?php if ($search !== ''): ?>
                            <a href="admin.php?view=bookings" class="clear-search"><i class='bx bx-x'></i></a>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-search">Search</button>
                </form>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Facility</th>
                                <th>Date</th>
                                <th>Time Slot</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($bookings_result && $bookings_result->num_rows > 0): ?>
                                <?php while ($row = $bookings_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['facility_name']) ?></td>
                                        <td><?= date('M d, Y', strtotime($row['booking_date'])) ?></td>
                                        <td><?= htmlspecialchars($row['time_slot']) ?></td>
                                        <td>
                                            <a href="admin.php?action=approve&type=booking&id=<?= $row['id'] ?>&search=<?= urlencode($search) ?>" class="btn-action approve"><i class='bx bx-check'></i> Accept</a>
                                            <a href="admin.php?action=reject&type=booking&id=<?= $row['id'] ?>&search=<?= urlencode($search) ?>" class="btn-action reject"><i class='bx bx-x'></i> Reject</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="no-data">No pending facility booking requests.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($view === 'schedules'): ?>
            <!-- SUB PAGE 4: MANAGE SCHEDULES & MATCHES -->
            <div class="admin-subcontainer">
                <div class="view-header">
                    <h2>Add New Match / Training Fixture</h2>
                    <a href="admin.php" class="btn-back"><i class='bx bx-arrow-back'></i> Back</a>
                </div>

                <form action="admin.php" method="POST" class="booking-form" style="max-width: 600px; margin: 0 auto 40px;">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 5px;">Event Title</label>
                        <input type="text" name="title" placeholder="e.g. NSBM vs UOC Friendly Match" required style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 5px;">Sport</label>
                        <input type="text" name="sport_type" placeholder="e.g. Basketball, Cricket, Badminton" required style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 5px;">Event Type</label>
                        <select name="event_type" required style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff;">
                            <option value="Match">Match</option>
                            <option value="Training">Training</option>
                            <option value="Tournament">Tournament</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 5px;">Date</label>
                        <input type="date" name="event_date" required style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 5px;">Time</label>
                        <input type="text" name="event_time" placeholder="e.g. 03:30 PM" required style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 5px;">Venue</label>
                        <input type="text" name="venue" placeholder="e.g. Main Indoor Court" required style="width: 100%; padding: 12px; border-radius: 8px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff;">
                    </div>
                    <button type="submit" name="add_schedule_btn" class="booking-submit-btn" style="width: 100%; padding: 14px; background: #0abf30; border: none; border-radius: 40px; color: white; font-weight: 700; cursor: pointer;">Add Match Fixture</button>
                </form>

                <div class="view-header" style="margin-top: 30px;">
                    <h2>Current Posted Fixtures</h2>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Event Title</th>
                                <th>Sport</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($schedules_list && $schedules_list->num_rows > 0): ?>
                                <?php while ($sch = $schedules_list->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($sch['title']) ?></strong></td>
                                        <td><?= htmlspecialchars($sch['sport_type']) ?></td>
                                        <td><?= htmlspecialchars($sch['event_type']) ?></td>
                                        <td><?= date('M d, Y', strtotime($sch['event_date'])) ?></td>
                                        <td><?= htmlspecialchars($sch['event_time']) ?></td>
                                        <td><?= htmlspecialchars($sch['venue']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-data">No schedules posted yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <script src="script.js"></script>
</body>
</html>