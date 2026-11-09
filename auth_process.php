<?php

session_start();
require_once 'config.php';

function getNsbmRole($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (str_ends_with($email, '@nsbm.ac.lk')) {
            return 'admin';
        }
        if (str_ends_with($email, '@students.nsbm.ac.lk')) {
            return 'student';
        }
    }
    return null;
}

if (isset($_POST['register_btn'])) {
    $name     = trim($_POST['name']);
    $email    = strtolower(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $assigned_role = getNsbmRole($email);

    if (!$assigned_role) {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Registration allowed only with official NSBM emails (@students.nsbm.ac.lk or @nsbm.ac.lk)!'
        ];
        $_SESSION['active_form'] = 'register';
        header('Location: index.php');
        exit();
    }

    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Email is already registered!'
        ];
        $_SESSION['active_form'] = 'register';
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("ssss", $name, $email, $password, $assigned_role);
        $insert_stmt->execute();

        $_SESSION['alerts'][] = [
            'type' => 'success',
            'message' => 'Registration successful! You can now log in.'
        ];
        $_SESSION['active_form'] = 'login';
    }

    header('Location: index.php');
    exit();
}

if (isset($_POST['login_btn'])) {
    $email    = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $nsbm_role = getNsbmRole($email);

    if (!$nsbm_role) {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Access denied! Please use your official NSBM email.'
        ];
        $_SESSION['active_form'] = 'login';
        header('Location: index.php');
        exit();
    }

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->num_rows > 0 ? $result->fetch_assoc() : null;

    if ($user && password_verify($password, $user['password'])) {
        $role = $user['role'] ?? $nsbm_role;

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $role;
        $_SESSION['alerts'][] = [
            'type' => 'success',
            'message' => 'Welcome back, ' . htmlspecialchars($user['name']) . '!'
        ];

        // DIRECT REDIRECT FOR ADMINS
        if ($role === 'admin') {
            header('Location: admin.php');
            exit();
        }

        // REDIRECT FOR STUDENTS
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['alerts'][] = [
            'type' => 'error',
            'message' => 'Incorrect email or password!'
        ];
        $_SESSION['active_form'] = 'login';
        header('Location: index.php');
        exit();
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}