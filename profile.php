<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

$name = $user['name'] ?? $_SESSION['name'] ?? 'User';
$email = $user['email'] ?? $_SESSION['email'] ?? 'Not set';
$role = $user['role'] ?? $_SESSION['role'] ?? 'student';

$club_stmt = $conn->prepare("SELECT club_name FROM user_clubs WHERE user_id = ? AND status = 'approved'");
$clubs_result = false;
if ($club_stmt) {
    $club_stmt->bind_param("i", $user_id);
    $club_stmt->execute();
    $clubs_result = $club_stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - NSBM SportsHub</title>
    <link rel="stylesheet" href="style.css?v=9.6">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body class="profile-page-body">
    <header>
        <a href="index.php" class="logo">
            <img src="logo2.png" alt="Logo">
        </a>
        <nav>
            <a href="index.php">Home</a>
            <a href="sports_clubs.php">Sports Clubs</a>
            <a href="schedules.php">Schedules & Matches</a>
            <a href="facility_booking.php">Facility Bookings</a>
        </nav>
        <div class="user-auth">
            <div class="profile-box">
                <div class="avatar-circle"><?= strtoupper($name[0]) ?></div>
                <div class="dropdown">
                    <a href="profile.php">My account</a>
                    <?php if ($role === 'admin'): ?>
                        <a href="admin.php">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <section class="profile-section">
        <div class="profile-card">
            <div class="profile-icon-circle">
                <i class='bx bx-user'></i>
            </div>

            <div class="info-pill">
                <span><?= htmlspecialchars($name) ?></span>
            </div>

            <div class="info-pill">
                <span><?= htmlspecialchars($email) ?></span>
            </div>

            <div class="unified-clubs-card">
                <div class="clubs-card-header">
                    <span>Joined Clubs</span>
                </div>
                <div class="clubs-card-body">
                    <?php if ($clubs_result && $clubs_result->num_rows > 0): ?>
                        <?php while ($club = $clubs_result->fetch_assoc()): ?>
                            <div class="club-item">
                                <span class="white-bullet">•</span>
                                <span><?= htmlspecialchars($club['club_name']) ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-clubs">No clubs joined yet. Explore the <a href="sports_clubs.php">Sports Clubs</a> page to join teams!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>