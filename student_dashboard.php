<?php
require_once 'includes/config.php';

if (!isLoggedIn() || getUserRole() !== 'student') {
    redirect('login.php');
}

$student_id = getStudentId();

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM semesters ORDER BY semester_number");
$stmt->execute();
$semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_semester = $_GET['semester'] ?? null;

if ($selected_semester) {
    $stmt = $pdo->prepare("
        SELECT r.*, s.subject_code, s.subject_name, s.max_marks, s.passing_marks, s.credit
        FROM results r
        JOIN subjects s ON r.subject_id = s.id
        WHERE r.student_id = ? AND r.semester_id = ?
        ORDER BY s.subject_code
    ");
    $stmt->execute([$student_id, $selected_semester]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM marksheets WHERE student_id = ? AND semester_id = ?");
    $stmt->execute([$student_id, $selected_semester]);
    $marksheets = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $pdo->prepare("
    SELECT m.*, s.semester_name, s.semester_number, s.academic_year
    FROM marksheets m
    JOIN semesters s ON m.semester_id = s.id
    WHERE m.student_id = ?
    ORDER BY s.semester_number
");
$stmt->execute([$student_id]);
$all_marksheets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Result System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <i class="fas fa-graduation-cap"></i>
            <span>Result System</span>
        </div>
        <div class="nav-right">
            <button class="theme-toggle" id="themeToggle">
                <i class="fas fa-moon"></i>
                <i class="fas fa-sun"></i>
            </button>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo $student['student_name']; ?></span>
            </div>
            <a href="logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>My Dashboard</h1>
            <p>Welcome back, <?php echo $student['student_name']; ?>!</p>
        </div>

        <div class="student-info-card">
            <div class="info-grid">
                <div class="info-item">
                    <label>Roll Number</label>
                    <span><?php echo $student['roll_number']; ?></span>
                </div>
                <div class="info-item">
                    <label>Course</label>
                    <span><?php echo $student['course']; ?></span>
                </div>
                <div class="info-item">
                    <label>Batch</label>
                    <span><?php echo $student['batch']; ?></span>
                </div>
                <div class="info-item">
                    <label>Enrollment Year</label>
                    <span><?php echo $student['enrollment_year']; ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($all_marksheets)): ?>
        <div class="section-card">
            <h2><i class="fas fa-chart-line"></i> CGPA Overview</h2>
            <div class="cgpa-chart">
                <?php foreach ($all_marksheets as $ms): ?>
                <div class="cgpa-bar">
                    <div class="bar-label">
                        <span>Sem <?php echo $ms['semester_number']; ?></span>
                        <span class="cgpa-value"><?php echo $ms['cgpa']; ?></span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: <?php echo ($ms['cgpa'] / 10) * 100; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="section-card">
            <h2><i class="fas fa-calendar-alt"></i> Select Semester</h2>
            <div class="semester-tabs">
                <?php foreach ($semesters as $sem): ?>
                <a href="?semester=<?php echo $sem['id']; ?>" 
                   class="semester-tab <?php echo $selected_semester == $sem['id'] ? 'active' : ''; ?>">
                    <span class="sem-number">Sem <?php echo $sem['semester_number']; ?></span>
                    <span class="sem-year"><?php echo $sem['academic_year']; ?></span>
                    <?php if ($sem['is_current']): ?>
                        <span class="current-badge">Current</span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($selected_semester && isset($results)): ?>
        <div class="section-card" id="resultSection">
            <div class="section-header">
                <h2><i class="fas fa-file-alt"></i> Marks Details</h2>
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Marksheet
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="data-table" id="marksTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Subject</th>
                            <th>Credits</th>
                            <th>Internal</th>
                            <th>External</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>GP</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $r): ?>
                        <tr>
                            <td><span class="badge"><?php echo $r['subject_code']; ?></span></td>
                            <td><?php echo $r['subject_name']; ?></td>
                            <td><?php echo $r['credit']; ?></td>
                            <td><?php echo $r['internal_marks']; ?>/40</td>
                            <td><?php echo $r['external_marks']; ?>/60</td>
                            <td class="total-marks"><?php echo $r['total_marks']; ?>/100</td>
                            <td><span class="grade grade-<?php echo strtolower(str_replace('+', 'plus', $r['grade'])); ?>"><?php echo $r['grade']; ?></span></td>
                            <td><?php echo $r['grade_point']; ?></td>
                            <td>
                                <span class="status status-<?php echo $r['status']; ?>">
                                    <?php echo ucfirst($r['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($marksheets): ?>
            <div class="marksheet-summary">
                <div class="summary-grid">
                    <div class="summary-item">
                        <i class="fas fa-calculator"></i>
                        <div>
                            <label>Total Marks</label>
                            <span><?php echo $marksheets['total_marks']; ?> / <?php echo $marksheets['max_possible_marks']; ?></span>
                        </div>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-percentage"></i>
                        <div>
                            <label>Percentage</label>
                            <span><?php echo $marksheets['percentage']; ?>%</span>
                        </div>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-star"></i>
                        <div>
                            <label>SGPA</label>
                            <span><?php echo $marksheets['sgpa']; ?></span>
                        </div>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-trophy"></i>
                        <div>
                            <label>CGPA</label>
                            <span><?php echo $marksheets['cgpa']; ?></span>
                        </div>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <label>Status</label>
                            <span class="status status-<?php echo $marksheets['result_status']; ?>">
                                <?php echo ucfirst($marksheets['result_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($all_marksheets)): ?>
        <div class="section-card">
            <h2><i class="fas fa-history"></i> All Semester Results</h2>
            <div class="marksheets-grid">
                <?php foreach ($all_marksheets as $ms): ?>
                <a href="?semester=<?php echo $ms['semester_id']; ?>" class="marksheet-card">
                    <div class="card-header">
                        <span class="sem-badge">Semester <?php echo $ms['semester_number']; ?></span>
                        <span class="year-badge"><?php echo $ms['academic_year']; ?></span>
                    </div>
                    <div class="card-body">
                        <div class="cgpa-display">
                            <span class="cgpa-number"><?php echo $ms['cgpa']; ?></span>
                            <span class="cgpa-label">CGPA</span>
                        </div>
                        <div class="card-stats">
                            <span><?php echo $ms['percentage']; ?>%</span>
                            <span class="status status-<?php echo $ms['result_status']; ?>">
                                <?php echo ucfirst($ms['result_status']); ?>
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
