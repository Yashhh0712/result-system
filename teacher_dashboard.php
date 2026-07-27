<?php
require_once 'includes/config.php';

if (!isLoggedIn() || (getUserRole() !== 'teacher' && getUserRole() !== 'admin')) {
    redirect('login.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add_result') {
            $student_id = intval($_POST['student_id']);
            $subject_id = intval($_POST['subject_id']);
            $semester_id = intval($_POST['semester_id']);
            $internal = intval($_POST['internal_marks']);
            $external = intval($_POST['external_marks']);
            $total = $internal + $external;
            
            $grade_point = 0;
            $grade = '';
            $status = 'pass';
            
            if ($total >= 90) { $grade = 'A+'; $grade_point = 10.0; }
            elseif ($total >= 80) { $grade = 'A'; $grade_point = 9.0; }
            elseif ($total >= 70) { $grade = 'B+'; $grade_point = 8.0; }
            elseif ($total >= 60) { $grade = 'B'; $grade_point = 7.0; }
            elseif ($total >= 50) { $grade = 'C+'; $grade_point = 6.0; }
            elseif ($total >= 40) { $grade = 'C'; $grade_point = 5.0; }
            elseif ($total >= 30) { $grade = 'D'; $grade_point = 4.0; }
            else { $grade = 'F'; $grade_point = 0.0; $status = 'fail'; }
            
            if ($total < 40) $status = 'fail';
            else $status = 'pass';
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO results (student_id, subject_id, semester_id, internal_marks, external_marks, grade, grade_point, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    internal_marks = VALUES(internal_marks),
                    external_marks = VALUES(external_marks),
                    grade = VALUES(grade),
                    grade_point = VALUES(grade_point),
                    status = VALUES(status),
                    updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$student_id, $subject_id, $semester_id, $internal, $external, $grade, $grade_point, $status]);
                $success = 'Result saved successfully!';
            } catch (Exception $e) {
                $error = 'Error saving result: ' . $e->getMessage();
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM students ORDER BY roll_number");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM semesters ORDER BY semester_number");
$stmt->execute();
$semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM subjects ORDER BY semester_id, subject_code");
$stmt->execute();
$all_subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_semester = $_GET['semester'] ?? null;
$selected_student = $_GET['student'] ?? null;

$existing_results = [];
if ($selected_semester && $selected_student) {
    $stmt = $pdo->prepare("
        SELECT r.*, s.subject_code, s.subject_name, s.max_marks
        FROM results r
        JOIN subjects s ON r.subject_id = s.id
        WHERE r.student_id = ? AND r.semester_id = ?
        ORDER BY s.subject_code
    ");
    $stmt->execute([$selected_student, $selected_semester]);
    $existing_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $pdo->prepare("
    SELECT r.*, st.roll_number, st.student_name, sub.subject_code, sub.subject_name, sem.semester_number, sem.academic_year
    FROM results r
    JOIN students st ON r.student_id = st.id
    JOIN subjects sub ON r.subject_id = sub.id
    JOIN semesters sem ON r.semester_id = sem.id
    ORDER BY sem.semester_number, st.roll_number, sub.subject_code
");
$stmt->execute();
$all_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Result System</title>
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
                <span><?php echo $_SESSION['full_name']; ?></span>
                <span class="role-badge"><?php echo ucfirst(getUserRole()); ?></span>
            </div>
            <a href="logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>Teacher Dashboard</h1>
            <p>Manage student results and marks</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="section-card">
            <h2><i class="fas fa-plus-circle"></i> Add/Edit Results</h2>
            <form method="POST" id="resultForm">
                <input type="hidden" name="action" value="add_result">
                <div class="form-row">
                    <div class="form-group">
                        <label for="semester_id">
                            <i class="fas fa-calendar"></i> Semester
                        </label>
                        <select name="semester_id" id="semesterSelect" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $sem): ?>
                            <option value="<?php echo $sem['id']; ?>" 
                                <?php echo $selected_semester == $sem['id'] ? 'selected' : ''; ?>>
                                <?php echo $sem['semester_name']; ?> (<?php echo $sem['academic_year']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="student_id">
                            <i class="fas fa-user-graduate"></i> Student
                        </label>
                        <select name="student_id" id="studentSelect" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $stu): ?>
                            <option value="<?php echo $stu['id']; ?>"
                                <?php echo $selected_student == $stu['id'] ? 'selected' : ''; ?>>
                                <?php echo $stu['roll_number']; ?> - <?php echo $stu['student_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject_id">
                            <i class="fas fa-book"></i> Subject
                        </label>
                        <select name="subject_id" id="subjectSelect" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($all_subjects as $sub): ?>
                            <option value="<?php echo $sub['id']; ?>" 
                                data-semester="<?php echo $sub['semester_id']; ?>">
                                [<?php echo $sub['subject_code']; ?>] <?php echo $sub['subject_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="internal_marks">
                            <i class="fas fa-pen"></i> Internal Marks (out of 40)
                        </label>
                        <input type="number" name="internal_marks" id="internalMarks" 
                               min="0" max="40" placeholder="0-40" required>
                    </div>
                    <div class="form-group">
                        <label for="external_marks">
                            <i class="fas fa-pen-fancy"></i> External Marks (out of 60)
                        </label>
                        <input type="number" name="external_marks" id="externalMarks" 
                               min="0" max="60" placeholder="0-60" required>
                    </div>
                    <div class="form-group preview-group">
                        <label>Total Preview</label>
                        <div class="marks-preview" id="marksPreview">
                            <span id="totalPreview">0</span> / 100
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Result
                </button>
            </form>
        </div>

        <?php if ($selected_semester && $selected_student): ?>
        <div class="section-card">
            <h2><i class="fas fa-list"></i> Current Results</h2>
            <?php if (!empty($existing_results)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Subject</th>
                            <th>Internal</th>
                            <th>External</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>GP</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($existing_results as $er): ?>
                        <tr>
                            <td><span class="badge"><?php echo $er['subject_code']; ?></span></td>
                            <td><?php echo $er['subject_name']; ?></td>
                            <td><?php echo $er['internal_marks']; ?></td>
                            <td><?php echo $er['external_marks']; ?></td>
                            <td class="total-marks"><?php echo $er['total_marks']; ?></td>
                            <td><span class="grade"><?php echo $er['grade']; ?></span></td>
                            <td><?php echo $er['grade_point']; ?></td>
                            <td>
                                <span class="status status-<?php echo $er['status']; ?>">
                                    <?php echo ucfirst($er['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="no-data">No results found for this student in this semester.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="section-card">
            <h2><i class="fas fa-database"></i> All Results</h2>
            <div class="filter-row">
                <input type="text" id="searchInput" placeholder="Search by name or roll number..." class="search-input">
            </div>
            <?php if (!empty($all_results)): ?>
            <div class="table-responsive">
                <table class="data-table" id="allResultsTable">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student</th>
                            <th>Sem</th>
                            <th>Subject</th>
                            <th>Internal</th>
                            <th>External</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_results as $ar): ?>
                        <tr class="result-row">
                            <td><?php echo $ar['roll_number']; ?></td>
                            <td><?php echo $ar['student_name']; ?></td>
                            <td>Sem <?php echo $ar['semester_number']; ?></td>
                            <td><span class="badge"><?php echo $ar['subject_code']; ?></span> <?php echo $ar['subject_name']; ?></td>
                            <td><?php echo $ar['internal_marks']; ?></td>
                            <td><?php echo $ar['external_marks']; ?></td>
                            <td class="total-marks"><?php echo $ar['total_marks']; ?></td>
                            <td><span class="grade"><?php echo $ar['grade']; ?></span></td>
                            <td>
                                <span class="status status-<?php echo $ar['status']; ?>">
                                    <?php echo ucfirst($ar['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="no-data">No results in the system yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/script.js"></script>
    <script>
    $(document).ready(function() {
        $('#semesterSelect').on('change', function() {
            var semId = $(this).val();
            $('#subjectSelect option').each(function() {
                if ($(this).val() == '') return;
                if ($(this).data('semester') == semId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#subjectSelect').val('');
            window.location.href = '?semester=' + semId + '&student=' + ($('#studentSelect').val() || '');
        });
        
        $('#studentSelect').on('change', function() {
            window.location.href = '?semester=' + ($('#semesterSelect').val() || '') + '&student=' + $(this).val();
        });
        
        $('#internalMarks, #externalMarks').on('input', function() {
            var internal = parseInt($('#internalMarks').val()) || 0;
            var external = parseInt($('#externalMarks').val()) || 0;
            var total = internal + external;
            $('#totalPreview').text(total);
            
            var preview = $('#marksPreview');
            preview.removeClass('preview-pass preview-fail');
            if (total >= 40) {
                preview.addClass('preview-pass');
            } else {
                preview.addClass('preview-fail');
            }
        });
        
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $("#allResultsTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });
    </script>
</body>
</html>
