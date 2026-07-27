<?php
require_once 'includes/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'get_subjects':
        $semester_id = intval($_GET['semester_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM subjects WHERE semester_id = ? ORDER BY subject_code");
        $stmt->execute([$semester_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
        
    case 'get_students':
        $stmt = $pdo->prepare("SELECT * FROM students ORDER BY roll_number");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
        
    case 'get_results':
        $student_id = intval($_GET['student_id'] ?? 0);
        $semester_id = intval($_GET['semester_id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT r.*, s.subject_code, s.subject_name, s.max_marks, s.passing_marks
            FROM results r
            JOIN subjects s ON r.subject_id = s.id
            WHERE r.student_id = ? AND r.semester_id = ?
            ORDER BY s.subject_code
        ");
        $stmt->execute([$student_id, $semester_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
        
    case 'get_marksheet':
        $student_id = intval($_GET['student_id'] ?? 0);
        $semester_id = intval($_GET['semester_id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT m.*, s.semester_name, s.semester_number, s.academic_year
            FROM marksheets m
            JOIN semesters s ON m.semester_id = s.id
            WHERE m.student_id = ? AND m.semester_id = ?
        ");
        $stmt->execute([$student_id, $semester_id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: new stdClass());
        break;
        
    case 'get_cgpa_history':
        $student_id = intval($_GET['student_id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT m.*, s.semester_name, s.semester_number, s.academic_year
            FROM marksheets m
            JOIN semesters s ON m.semester_id = s.id
            WHERE m.student_id = ?
            ORDER BY s.semester_number
        ");
        $stmt->execute([$student_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
        
    case 'delete_result':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result_id = intval($_POST['result_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM results WHERE id = ?");
            $stmt->execute([$result_id]);
            echo json_encode(['success' => true]);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
