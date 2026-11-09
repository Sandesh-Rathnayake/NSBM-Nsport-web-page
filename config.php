<?php
$host = 'sql302.infinityfree.com';
$user = 'if0_43388434';
$password = 'Sandesh12345678'; 
$database = 'if0_43388434_users_db'; 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>