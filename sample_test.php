<?php
session_start();
require_once "../services/GroqService.php";

$dayTopic = "Day 1: Arrays, Linked Lists, Stacks, Queues";

$groq = new GroqService();
$data = $groq->generateDailyTest($dayTopic);

$questions = $data['questions'] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Day 1 Sample Test | SkillLens</title>
    <link rel="stylesheet" href="/resume-skill-analyzer/assets/css/style.css">
</head>
<body>

<?php include '../includes/dashboard_header.php'; ?>

<div class="app-bg">
    <div class="app-card">

        <h2>📝 Day 1 Sample Test</h2>
        <p class="subtitle">Answer all questions to complete your streak</p>

        <form action="submit_test.php" method="POST">
            <?php foreach ($questions as $index => $q): ?>
                <div class="card-box">
                    <h3><?= ($index+1) . ". " . htmlspecialchars($q['question']) ?></h3>

                    <?php foreach ($q['options'] as $optIndex => $opt): ?>
                        <label style="display:block;margin:6px 0;">
                            <input type="radio"
                                   name="answers[<?= $index ?>]"
                                   value="<?= $optIndex ?>"
                                   required>
                            <?= htmlspecialchars($opt) ?>
                        </label>
                    <?php endforeach; ?>

                    <input type="hidden"
                           name="correct[<?= $index ?>]"
                           value="<?= $q['answer'] ?>">
                </div>
            <?php endforeach; ?>

            <button class="dashboard-cta" type="submit">
                ✅ Submit Test
            </button>
        </form>

    </div>
</div>

</body>
</html>