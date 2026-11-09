<?php
session_start();
require_once 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$name = $_SESSION['name'] ?? null;

// Handle Form Submission with Lahiruni's Validation Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    if (!$user_id) {
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Please login to submit a facility booking!'];
        header('Location: facility_booking.php');
        exit();
    }

    $facility_name = trim($_POST['facility_name'] ?? '');
    $booking_date  = trim($_POST['booking_date'] ?? '');
    $time_slot     = trim($_POST['time_slot'] ?? '');
    $purpose       = trim($_POST['purpose'] ?? '');

    // List of ALL 12 Campus Sports & Gear Facilities
    $allowed_facilities = [
        'Athletics Track & Field Grid',
        'Badminton Court A (Indoor)',
        'Badminton Court B (Indoor)',
        'Chess Gear Set & Board',
        'Main Cricket Pitch & Nets',
        'Martial Arts Dojo Room',
        'Netball Court (Outdoor)',
        'Powerlifting Gym Section',
        'Rugby Training Ground',
        'Swimming Pool (Lap Lanes)',
        'Table Tennis Gear & Arena',
        'Tennis Court A (Outdoor)',
        'Volleyball Court'
    ];

    $allowed_slots = [
        '08:00 - 10:00',
        '10:00 - 12:00',
        '12:00 - 14:00',
        '14:00 - 16:00',
        '16:00 - 18:00',
        '18:00 - 20:00'
    ];

    // 1. Validation Checks
    if (!in_array($facility_name, $allowed_facilities, true) || 
        !in_array($time_slot, $allowed_slots, true) || 
        empty($booking_date) || empty($purpose)) {
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Please fill in all fields with valid options!'];
    } elseif ($booking_date < date('Y-m-d')) {
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Booking date cannot be in the past!'];
    } else {
        // 2. Lahiruni's Double-Booking Check
        $check = $conn->prepare("
            SELECT id FROM facility_bookings 
            WHERE facility_name = ? AND booking_date = ? AND time_slot = ? 
            AND status IN ('pending', 'approved')
        ");
        $check->bind_param("sss", $facility_name, $booking_date, $time_slot);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'That facility is already reserved for the selected date and time slot!'];
        } else {
            // 3. Insert Booking Request
            $stmt = $conn->prepare("
                INSERT INTO facility_bookings (user_id, facility_name, booking_date, time_slot, purpose, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->bind_param("issss", $user_id, $facility_name, $booking_date, $time_slot, $purpose);
            
            if ($stmt->execute()) {
                $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Booking request submitted! Waiting for admin approval.'];
            } else {
                $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Failed to process request. Please try again.'];
            }
        }
    }

    header('Location: facility_booking.php');
    exit();
}

// Fetch this student's past & current facility bookings
$user_bookings = [];
if ($user_id) {
    $stmt = $conn->prepare("
        SELECT * FROM facility_bookings 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$alerts = $_SESSION['alerts'] ?? [];
unset($_SESSION['alerts']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility Bookings - NSBM SportsHub</title>
    <link rel="stylesheet" href="style.css?v=9.9">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body class="profile-page-body">

    <!-- NAVIGATION HEADER -->
    <header>
        <a href="index.php" class="logo">
            <img src="logo2.png" alt="Logo">
        </a>
        <nav>
            <a href="index.php">Home</a>
            <a href="sports_clubs.php">Sports Clubs</a>
            <a href="schedules.php">Schedules & Matches</a>
            <a href="facility_booking.php" style="color: #0abf30;">Facility Bookings</a>
        </nav>
        <div class="user-auth">
            <?php if (!empty($name)): ?>
            <div class="profile-box">
                <div class="avatar-circle"><?= strtoupper($name[0]) ?></div>
                <div class="dropdown">
                    <a href="profile.php">My account</a>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <a href="admin.php">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
            <?php else: ?>
            <button type="button" class="login-btn-modal">Login</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- BOOKING FORM SECTION -->
    <section class="booking-section">
        <div class="booking-card">
            <h2>Book Campus Facility</h2>
            <p class="sub-text">Select a facility, date, and time slot. Campus staff will review your request.</p>

            <form action="facility_booking.php" method="POST" class="booking-form">
                <div class="form-group">
                    <label for="facility_name">Facility / Equipment</label>
                    <select name="facility_name" id="facility_name" required>
                        <option value="">Select a Sport Facility</option>
                        <option value="Athletics Track & Field Grid">Athletics Track & Field Grid</option>
                        <option value="Badminton Court A (Indoor)">Badminton Court A (Indoor)</option>
                        <option value="Badminton Court B (Indoor)">Badminton Court B (Indoor)</option>
                        <option value="Chess Gear Set & Board">Chess Gear Set & Board</option>
                        <option value="Main Cricket Pitch & Nets">Main Cricket Pitch & Nets</option>
                        <option value="Martial Arts Dojo Room">Martial Arts Dojo Room</option>
                        <option value="Netball Court (Outdoor)">Netball Court (Outdoor)</option>
                        <option value="Powerlifting Gym Section">Powerlifting Gym Section</option>
                        <option value="Rugby Training Ground">Rugby Training Ground</option>
                        <option value="Swimming Pool (Lap Lanes)">Swimming Pool (Lap Lanes)</option>
                        <option value="Table Tennis Gear & Arena">Table Tennis Gear & Arena</option>
                        <option value="Tennis Court A (Outdoor)">Tennis Court A (Outdoor)</option>
                        <option value="Volleyball Court">Volleyball Court</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="booking_date">Booking Date</label>
                    <input type="date" name="booking_date" id="booking_date" required>
                </div>

                <div class="form-group">
                    <label for="time_slot">Time Slot</label>
                    <select name="time_slot" id="time_slot" required>
                        <option value="">Select Time Slot</option>
                        <option value="08:00 - 10:00">08:00 - 10:00 AM</option>
                        <option value="10:00 - 12:00">10:00 - 12:00 PM</option>
                        <option value="12:00 - 02:00">12:00 - 02:00 PM</option>
                        <option value="14:00 - 16:00">02:00 - 04:00 PM</option>
                        <option value="16:00 - 18:00">04:00 - 06:00 PM</option>
                        <option value="18:00 - 20:00">06:00 - 08:00 PM</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="purpose">Purpose / Description</label>
                    <textarea name="purpose" id="purpose" maxlength="1000" placeholder="e.g. Inter-faculty practice match, team workout" required></textarea>
                </div>

                <button type="submit" name="submit_booking" class="booking-submit-btn">Send Booking Request</button>
            </form>
        </div>

        <!-- USER'S BOOKING STATUS LIST -->
        <?php if (!empty($user_bookings)): ?>
        <div class="admin-subcontainer" style="margin-top: 35px;">
            <div class="view-header">
                <h2><i class='bx bx-time-five'></i> My Booking History</h2>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Facility</th>
                            <th>Date</th>
                            <th>Slot</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_bookings as $booking): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($booking['facility_name']) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                            <td><?= htmlspecialchars($booking['time_slot']) ?></td>
                            <td>
                                <span class="badge <?= htmlspecialchars($booking['status']) ?>">
                                    <?= htmlspecialchars($booking['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <!-- TOAST ALERTS -->
    <?php if (!empty($alerts)): ?>
    <div class="alert-box">
        <?php foreach ($alerts as $alert): ?>
        <div class="alert <?= $alert['type'] ?>">
            <i class='bx <?= $alert['type'] === 'success' ? 'bxs-check-circle' : 'bxs-x-circle'; ?>'></i>
            <span><?= htmlspecialchars($alert['message']); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- AUTH MODAL FOR FACILITY BOOKINGS PAGE -->
    <div class="auth-modal">
        <button type="button" class="close-btn-modal"><i class="bx bx-x-circle"></i></button>
        <div class="form-box login">
            <h2>Login</h2>
            <form action="auth_process.php" method="POST">
                <div class="input-box">
                    <input type="email" name="email" placeholder="name@students.nsbm.ac.lk" required>
                    <i class="bx bx-envelope"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="bx bx-lock-alt"></i>
                </div>
                <button type="submit" name="login_btn" class="btn">Login</button>
                <p>Don't have an account? <a href="#" class="register-link">Register</a></p>
            </form>
        </div>

        <div class="form-box register">
            <h2>Register</h2>
            <form action="auth_process.php" method="POST">
                <div class="input-box">
                    <input type="text" name="name" placeholder="Name" required>
                    <i class="bx bx-user"></i>
                </div>
                <div class="input-box">
                    <input type="email" name="email" placeholder="name@students.nsbm.ac.lk" required>
                    <i class="bx bx-envelope"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="bx bx-lock-alt"></i>
                </div>
                <button type="submit" name="register_btn" class="btn">Register</button>
                <p>Already have an account? <a href="#" class="login-link">Login</a></p>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Lahiruni's JS logic: Restrict past dates in date picker
        document.getElementById('booking_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>