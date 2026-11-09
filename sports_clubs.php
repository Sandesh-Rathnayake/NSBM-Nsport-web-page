<?php
session_start();
require_once 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$name = $_SESSION['name'] ?? null;

$alerts = $_SESSION['alerts'] ?? [];
unset($_SESSION['alerts']);

if (isset($_POST['join_btn'])) {
    $club_name = trim($_POST['club_name'] ?? '');

    if (!$user_id) {
        $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Please login first!'];
    } else {
        $check = $conn->prepare("SELECT id FROM clubs WHERE club_name = ?");
        $check->bind_param("s", $club_name);
        $check->execute();
        $club_exists = $check->get_result()->num_rows > 0;

        if (!$club_exists) {
            $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'Please choose a sport from the list.'];
        } else {
            $old = $conn->prepare("SELECT status FROM user_clubs WHERE user_id = ? AND club_name = ?");
            $old->bind_param("is", $user_id, $club_name);
            $old->execute();
            $old_row = $old->get_result()->fetch_assoc();

            if (!$old_row) {
                $stmt = $conn->prepare("INSERT INTO user_clubs (user_id, club_name, status) VALUES (?, ?, 'pending')");
                $stmt->bind_param("is", $user_id, $club_name);
                $stmt->execute();
                $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Request sent! Waiting for approval.'];
            } elseif ($old_row['status'] === 'rejected') {
                $stmt = $conn->prepare("UPDATE user_clubs SET status = 'pending' WHERE user_id = ? AND club_name = ?");
                $stmt->bind_param("is", $user_id, $club_name);
                $stmt->execute();
                $_SESSION['alerts'][] = ['type' => 'success', 'message' => 'Request sent again!'];
            } else {
                $_SESSION['alerts'][] = ['type' => 'error', 'message' => 'You already requested ' . $club_name . ' (' . $old_row['status'] . ').'];
            }
        }
    }

    header('Location: sports_clubs.php');
    exit();
}

$clubs = [];
$result = $conn->query("SELECT club_name, description, icon, image FROM clubs ORDER BY club_name");
while ($row = $result->fetch_assoc()) {
    $clubs[] = $row;
}

$my_requests = [];
if ($user_id) {
    $stmt = $conn->prepare("SELECT club_name, status FROM user_clubs WHERE user_id = ? ORDER BY joined_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $my_result = $stmt->get_result();
    while ($row = $my_result->fetch_assoc()) {
        $my_requests[$row['club_name']] = $row['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Clubs - NSBM SportsHub</title>
    <link rel="stylesheet" href="style.css?v=9.9">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <a href="index.php" class="logo">
            <img src="logo2.png" alt="Logo">
        </a>
        <nav>
            <a href="index.php">Home</a>
            <a href="sports_clubs.php" style="color: #0abf30;">Sports Clubs</a>
            <a href="schedules.php">Schedules & Matches</a>
            <a href="facility_booking.php">Facility Bookings</a>
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

    <section class="clubs-hero">
        <h1>Sports Clubs</h1>
        <p>Find your sport, send a request to join, and wait for approval from the club.</p>
    </section>

    <main class="clubs-main">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search for a sport...">
            <i class="bx bx-search"></i>
        </div>

        <div class="sports-grid" id="sportsGrid">
            <?php foreach ($clubs as $club): ?>
                <?php 
                    $status = $my_requests[$club['club_name']] ?? null; 
                    $card_img = !empty($club['image']) ? $club['image'] : 'background.jpg';
                ?>
                <div class="sport-card" 
                     data-name="<?= htmlspecialchars(strtolower($club['club_name'])) ?>"
                     style="background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.75)), url('<?= htmlspecialchars($card_img) ?>') no-repeat center center/cover;">
                    <i class="bx <?= htmlspecialchars($club['icon']) ?> sport-icon"></i>
                    <h3><?= htmlspecialchars($club['club_name']) ?></h3>
                    <p><?= htmlspecialchars($club['description']) ?></p>

                    <?php if ($status): ?>
                        <span class="badge <?= $status ?>"><?= $status ?></span>
                    <?php endif; ?>

                    <?php if (!$status || $status === 'rejected'): ?>
                        <button type="button" class="select-btn" data-club="<?= htmlspecialchars($club['club_name']) ?>">Select</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="no-results" id="noResults">No sports found. Try another name.</p>

        <div class="join-section" id="join-form">
            <h2>Request to Join</h2>
            <p class="note">
                <?= $user_id ? 'Choose a sport and send your request.' : 'Please login to send a request.' ?>
            </p>
            <form action="sports_clubs.php" method="POST">
                <label for="club_name">Sport</label>
                <select name="club_name" id="club_name" required>
                    <option value="">Choose a sport</option>
                    <?php foreach ($clubs as $club): ?>
                        <option value="<?= htmlspecialchars($club['club_name']) ?>"><?= htmlspecialchars($club['club_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="join_btn" class="join-btn">Request to Join</button>
            </form>
        </div>

        <?php if (!empty($my_requests)): ?>
        <div class="my-requests">
            <h2>My Requests</h2>
            <?php foreach ($my_requests as $club_name => $status): ?>
                <div class="request-row">
                    <span><?= htmlspecialchars($club_name) ?></span>
                    <span class="badge <?= $status ?>"><?= $status ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

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

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-about">
                <h3>NSBM Sports Club Circle</h3>
                <p>Your ultimate destination for campus athletics, club management, match schedules, and sports facility bookings.</p>
            </div>

            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="sports_clubs.php">Sports Clubs</a></li>
                    <li><a href="schedules.php">Schedules & Matches</a></li>
                    <li><a href="facility_booking.php">Facility Bookings</a></li>
                </ul>
            </div>

            <div class="footer-social">
                <h4>Follow Us</h4>
                <div class="social-icons">
                    <a href="https://www.facebook.com/nsbm.lk/" target="_blank" title="Facebook"><i class='bx bxl-facebook'></i></a>
                    <a href="https://www.instagram.com/nsbmgreenuniversity/?hl=en" target="_blank" title="Instagram"><i class='bx bxl-instagram'></i></a>
                    <a href="https://www.youtube.com/@NSBMGreenUniversityTown" target="_blank" title="YouTube"><i class='bx bxl-youtube'></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y'); ?> NSBM Sports Club Circle. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        var searchInput = document.getElementById('searchInput');
        var cards = document.querySelectorAll('.sport-card');
        var noResults = document.getElementById('noResults');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var text = searchInput.value.toLowerCase().trim();
                var shown = 0;

                cards.forEach(function (card) {
                    if (card.dataset.name.indexOf(text) !== -1) {
                        card.style.display = '';
                        shown++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                noResults.style.display = shown === 0 ? 'block' : 'none';
            });
        }

        document.querySelectorAll('.select-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById('club_name').value = button.dataset.club;
                document.getElementById('join-form').scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>