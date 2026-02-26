<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | SkillLens</title>
    <link rel="stylesheet" href="/resume-skill-analyzer/assets/css/style.css">
</head>

<body>

<!-- HEADER -->
<?php include '../includes/dashboard_header.php'; ?>

<!-- MAIN DASHBOARD CONTENT -->
<div class="app-bg">
    <div class="app-card dashboard-card">

        <h2 class="dashboard-title">
            Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋
        </h2>
        <p class="dashboard-subtitle">
            Your Resume Skill Analyzer Dashboard
        </p>

        <!-- FORM START -->
        <form action="../controllers/ResumeController.php"
              method="POST"
              enctype="multipart/form-data">

            <div class="dashboard-grid">

                <!-- Upload Resume -->
                <div class="card-box card-row">
                    <div class="card-info">
                        <h3 class="card-title">📄 Upload Your Resume</h3>
                        <p class="card-text">
                            Upload your resume in PDF or DOCX format.
                        </p>
                    </div>
                    <div class="card-action">
                        <input type="file"
                               name="resume"
                               class="upload-input"
                               required>
                    </div>
                </div>

                <!-- Target Job Role -->
                <div class="card-box card-row">
                    <div class="card-info">
                        <h3 class="card-title">🎯 Target Job Role</h3>
                        <p class="card-text">
                            Select the role you are preparing for.
                        </p>
                    </div>
                    <div class="card-action">
                        <select name="job_role"
                                class="upload-input"
                                required>
                            <option value="">Select role</option>
                            <option>Data Analyst</option>
                            <option>Software Developer</option>
                            <option>Web Developer</option>
                            <option>Business Analyst</option>
                        </select>
                    </div>
                </div>

                <!-- Analyze Resume (PRIMARY CTA) -->
                <div class="card-box highlight-card">
                    <h3 class="card-title">🚀 Analyze Resume</h3>
                    <p class="card-text">
                        Get skill gap analysis and a 30-day upskilling plan.
                    </p>
                    <button class="dashboard-cta"
                            type="submit"
                            name="analyze">
                        Analyze Resume
                    </button>
                </div>

            </div>
        </form>
        <!-- FORM END -->

    </div>
</div>

<!-- FOOTER -->
<?php include '../includes/footer.php'; ?>

</body>
</html>