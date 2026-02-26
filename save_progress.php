<?php
session_start();
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id   = (int) $_SESSION['user_id'];
$resume_id = (int)($_POST['resume_id'] ?? ($_SESSION['active_resume_id'] ?? 0));
$day_number = (int) ($_POST['day_number'] ?? 0);

if ($resume_id <= 0 || $day_number <= 0) {
    die("Invalid request");
}

/* ------------------------------------
   DB CONNECTION
------------------------------------ */
$db = new Database();
$conn = $db->connect();

/* ------------------------------------
   PREVENT DUPLICATES
------------------------------------ */
$check = $conn->prepare(
    "SELECT id
     FROM daily_progress
     WHERE user_id = ? AND resume_id = ? AND day_number = ?"
);
$check->bind_param("iii", $user_id, $resume_id, $day_number);
$check->execute();
$result = $check->get_result();

/* ------------------------------------
   INSERT OR UPDATE
------------------------------------ */
if ($result->num_rows === 0) {
    $stmt = $conn->prepare(
        "INSERT INTO daily_progress
         (user_id, resume_id, day_number, completed, completed_at)
         VALUES (?, ?, ?, 1, NOW())"
    );
    $stmt->bind_param("iii", $user_id, $resume_id, $day_number);
    $stmt->execute();
} else {
    $row = $result->fetch_assoc();
    $stmt = $conn->prepare(
        "UPDATE daily_progress
         SET completed = 1, completed_at = NOW()
         WHERE id = ?"
    );
    $stmt->bind_param("i", $row['id']);
    $stmt->execute();
}

/* ------------------------------------
   REDIRECT TO NEXT DAY
------------------------------------ */
// Preserve resume reference and move to next day
$_SESSION['active_resume_day'] = $day_number + 1;
header("Location: progress.php?day=" . ($day_number + 1) . "&rid=" . $resume_id);
exit;