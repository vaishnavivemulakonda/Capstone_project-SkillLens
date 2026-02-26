<?php
require_once "../config/db.php";

if (isset($_POST['signup'])) {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = $_POST['role'];

    /* 1️⃣ Basic Validation */
    if ($password !== $confirm) {
        die("Passwords do not match");
    }

    if (strlen($password) < 8) {
        die("Password must be at least 8 characters");
    }

    /* 2️⃣ Database Connection */
    $db = new Database();
    $conn = $db->connect();

    /* 3️⃣ Check if Email Already Exists */
    $checkQuery = $conn->prepare(
        "SELECT id FROM users WHERE email = ?"
    );
    $checkQuery->bind_param("s", $email);
    $checkQuery->execute();
    $checkQuery->store_result();

    if ($checkQuery->num_rows > 0) {
        die("Email already registered");
    }

    /* 4️⃣ Secure Password Hashing */
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    /* 5️⃣ Insert User (Prepared Statement) */
    $insertQuery = $conn->prepare(
        "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
    );
    $insertQuery->bind_param(
        "ssss",
        $name,
        $email,
        $hashedPassword,
        $role
    );

    if ($insertQuery->execute()) {
        header("Location: ../public/login.php?signup=success");
        exit;
    } else {
        die("Registration failed");
    }
}
?>
<?php
require_once "../config/db.php";

/* =========================
   SIGNUP LOGIC
   ========================= */
if (isset($_POST['signup'])) {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = $_POST['role'];

    // Basic validation
    if ($password !== $confirm) {
        die("Passwords do not match");
    }

    if (strlen($password) < 8) {
        die("Password must be at least 8 characters");
    }

    $db = new Database();
    $conn = $db->connect();

    // Check if email already exists
    $checkQuery = $conn->prepare(
        "SELECT id FROM users WHERE email = ?"
    );
    $checkQuery->bind_param("s", $email);
    $checkQuery->execute();
    $checkQuery->store_result();

    if ($checkQuery->num_rows > 0) {
        die("Email already registered");
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $insertQuery = $conn->prepare(
        "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
    );
    $insertQuery->bind_param(
        "ssss",
        $name,
        $email,
        $hashedPassword,
        $role
    );

    if ($insertQuery->execute()) {
        header("Location: ../public/login.php?signup=success");
        exit;
    } else {
        die("Registration failed");
    }
}

/* =========================
   LOGIN LOGIC
   ========================= */
if (isset($_POST['login'])) {

    session_start();

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $db = new Database();
    $conn = $db->connect();

    $query = $conn->prepare(
        "SELECT id, name, password FROM users WHERE email = ?"
    );
    $query->bind_param("s", $email);
    $query->execute();

    // Bind result variables
    $query->bind_result($id, $name, $hashed_password);

    if ($query->fetch()) {

        if (password_verify($password, $hashed_password)) {

            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;

            header("Location: ../public/dashboard.php");
            exit;

        } else {
            die("Invalid password");
        }

    } else {
        die("User not found");
    }
}