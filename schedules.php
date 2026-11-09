<?php
session_start();
require_once 'config.php';

// Fetch match schedules
$schedules_result = $conn->query("
    SELECT * FROM matches_schedules 
    ORDER BY event_date ASC
");

// Combined query: Fetch approved facility bookings
$approved_bookings = $conn->query("
    SELECT fb.facility_name, fb.booking_date, fb.time_slot, u.name as booked_by
    FROM facility_bookings fb
    JOIN users u ON fb.user_id = u.id
    WHERE fb.status = 'approved' AND fb.booking_date >= CURDATE()
    ORDER BY fb.booking_date ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedules & Matches - NSBM SportsHub</title>
    <link rel="stylesheet" href="style.css?v=9.6">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body class="profile-page-body">

    <!-- HEADER -->
    <header>
        <a href="index.php" class="logo">
            <img src="logo2.png" alt="Logo">
        </a>
        <nav>
            <a href="index.php">Home</a>
            <a href="sports_clubs.php">Sports Clubs</a>
            <a href="schedules.php" style="color: #0abf30;">Schedules & Matches</a>
            <a href="facility_booking.php">Facility Bookings</a>
        </nav>
        <div class="user-auth">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="profile-box">
                    <div class="avatar-circle"><?= strtoupper($_SESSION['name'][0] ?? 'U') ?></div>
                    <div class="dropdown">
                        <a href="profile.php">My Account</a>
                        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                            <a href="admin.php">Admin Panel</a>
                        <?php endif; ?>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <button class="login-btn-modal">Login</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- SCHEDULES & MATCHES SECTION -->
    <section class="profile-section">
        <div class="admin-subcontainer" style="max-width: 1000px; margin-top: 40px;">
            <div class="view-header">
                <h2><i class='bx bx-calendar-event'></i> Upcoming Matches & Training Fixtures</h2>
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
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($schedules_result && $schedules_result->num_rows > 0): ?>
                            <?php while ($row = $schedules_result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['sport_type']) ?></td>
                                    <td><span class="type-pill"><?= htmlspecialchars($row['event_type']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($row['event_date'])) ?></td>
                                    <td><?= htmlspecialchars($row['event_time']) ?></td>
                                    <td><?= htmlspecialchars($row['venue']) ?></td>
                                    <td><span class="status-tag <?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">No upcoming matches or schedules posted yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- COMBINED APPROVED FACILITY RESERVATIONS -->
            <div class="view-header" style="margin-top: 40px;">
                <h2><i class='bx bx-building-house'></i> Reserved Facility Availability</h2>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Reserved Venue</th>
                            <th>Reserved Date</th>
                            <th>Time Slot</th>
                            <th>Reserved By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($approved_bookings && $approved_bookings->num_rows > 0): ?>
                            <?php while ($booking = $approved_bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['facility_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                                    <td><?= htmlspecialchars($booking['time_slot']) ?></td>
                                    <td><?= htmlspecialchars($booking['booked_by']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data">No facilities currently reserved. All grounds available!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>