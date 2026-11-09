<?php

session_start();

$user_id = $_SESSION['user_id'] ?? null;
$name = $_SESSION['name'] ?? null;
$email = $_SESSION['email'] ?? null;
$role = $_SESSION['role'] ?? 'student';

$alerts = $_SESSION['alerts'] ?? [];
$active_form = $_SESSION['active_form'] ?? '';

unset($_SESSION['alerts']);
unset($_SESSION['active_form']);

?>
<!DOCTYPE html>
<html lang="en">   
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSBM SportsHub</title>
    <link rel="stylesheet" href="style.css?v=9.9">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <a href="index.php" class="logo">
            <img src="logo2.png" alt="Logo">
        </a>
        <nav>
            <a href="index.php" style="color: #0abf30;">Home</a>
            <a href="sports_clubs.php">Sports Clubs</a>
            <a href="schedules.php">Schedules & Matches</a>
            <a href="facility_booking.php">Facility Bookings</a>
        </nav>
        <div class="user-auth">
            <?php if (!empty($name)): ?>
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
            <?php else: ?>
            <button type="button" class="login-btn-modal">Login</button>
            <?php endif; ?>
        </div>
    </header>
    
    <section class="hero-section">
        <h1>NSBM University Sports<br>Clubs Circle</h1>
        <p>Join sports clubs, keep track of match schedules, participate in tournaments, and reserve campus sports facilities.</p>
        
        <div class="hero-buttons">
            <a href="sports_clubs.php" class="hero-btn primary"><i class='bx bx-group'></i> Browse Clubs</a>
            <a href="schedules.php" class="hero-btn secondary"><i class='bx bx-calendar'></i> View Fixtures</a>
        </div>
    </section>

    <section class="cards-section">
        <div class="card-container">
            <a href="sports_clubs.php" class="card-link">
                <div class="card">
                    <div class="card-icon icon-green">
                        <i class='bx bxs-group'></i>
                    </div>
                    <h3>Club Management</h3>
                    <p>Explore active sports teams, apply for memberships, and get official approvals.</p>
                </div>
            </a>

            <a href="schedules.php" class="card-link">
                <div class="card">
                    <div class="card-icon icon-blue">
                        <i class='bx bxs-calendar-event'></i>
                    </div>
                    <h3>Schedules & Matches</h3>
                    <p>Stay up to date with training practice sessions and competitive fixtures.</p>
                </div>
            </a>

            <a href="facility_booking.php" class="card-link">
                <div class="card">
                    <div class="card-icon icon-yellow">
                        <i class='bx bx-dumbbell'></i>
                    </div>
                    <h3>Facility Bookings</h3>
                    <p>Reserve grounds, indoor courts, and equipment directly online.</p>
                </div>
            </a>
        </div>
    </section>

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

    <div class="auth-modal <?= $active_form === 'register' ? 'show slide' : ($active_form === 'login' ? 'show' : ''); ?>">
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
</body>
</html>