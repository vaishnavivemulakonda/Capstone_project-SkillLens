<?php
session_start();

/* ===============================
   SECURITY CHECK
   =============================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

require_once "../config/db.php";
require_once __DIR__ . "/../vendor/autoload.php";
require_once "../services/GroqService.php";

/* ===============================
   STEP-2: RESUME TEXT EXTRACTION
   =============================== */
function extractResumeText(string $filePath, string $ext): string {

    if ($ext === 'pdf') {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filePath);
        return trim(strtolower($pdf->getText()));
    }

    if ($ext === 'docx') {
        $content = '';
        $zip = zip_open($filePath);

        if ($zip) {
            while ($zip_entry = zip_read($zip)) {
                if (zip_entry_name($zip_entry) === "word/document.xml") {
                    zip_entry_open($zip, $zip_entry);
                    $content = zip_entry_read($zip, zip_entry_filesize($zip_entry));
                    zip_entry_close($zip_entry);
                }
            }
            zip_close($zip);
        }

        return trim(strtolower(strip_tags($content)));
    }

    return '';
}

/* ===============================
   HANDLE ANALYZE REQUEST
   =============================== */
if (!isset($_POST['analyze'])) {
    die("Invalid request");
}

$user_id  = $_SESSION['user_id'];
$job_role = $_POST['job_role'] ?? '';

if ($job_role === '') {
    die("Job role not selected");
}

/* ===============================
   RESUME VALIDATION
   =============================== */
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== 0) {
    die("Resume upload failed");
}

$file = $_FILES['resume'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['pdf', 'docx'])) {
    die("Only PDF or DOCX allowed");
}

/* ===============================
   UPLOAD RESUME
   =============================== */
$uploadDir = dirname(__DIR__) . "/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "", $file['name']);
$filePath = $uploadDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    die("Failed to save resume");
}

/* ===============================
   EXTRACT RESUME TEXT (CRITICAL)
   =============================== */
$resumeText = extractResumeText($filePath, $ext);

if (empty($resumeText)) {
    die("Resume text extraction failed");
}

/* ===============================
   DATABASE CONNECTION
   =============================== */
$db   = new Database();
$conn = $db->connect();

/* ===============================
   SAVE RESUME (UNIQUE PER UPLOAD)
   =============================== */
$stmt = $conn->prepare(
    "INSERT INTO resume_uploads (user_id, file_name)
     VALUES (?, ?)"
);
$stmt->bind_param("is", $user_id, $fileName);
$stmt->execute();

$resume_id = $stmt->insert_id;

/* ===============================
   AI ANALYSIS (REAL & DYNAMIC)
   =============================== */
$groq = new GroqService();
$aiResult = $groq->analyzeResume($resumeText, $job_role);

if (!is_array($aiResult) || isset($aiResult['error'])) {
    die("AI analysis failed");
}

/* ===============================
   STORE AI RESULT (ROLE + RESUME SPECIFIC)
   =============================== */
$stmt = $conn->prepare(
    "INSERT INTO resume_analysis
     (user_id, resume_id, job_role, ai_result)
     VALUES (?, ?, ?, ?)"
);

$jsonResult = json_encode($aiResult, JSON_UNESCAPED_UNICODE);
$stmt->bind_param("iiss", $user_id, $resume_id, $job_role, $jsonResult);
$stmt->execute();

/* ===============================
   REDIRECT TO RESULT PAGE
   =============================== */
header("Location: ../public/analysis_result.php?rid=" . $resume_id);
exit;