<?php
session_start();

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}

include("connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $courseID = isset($_POST['G_courseID']) ? intval($_POST['G_courseID']) : 0;
    $term = isset($_POST['G_term']) ? intval($_POST['G_term']) : 0;
    $scores = isset($_POST['G_num']) && is_array($_POST['G_num']) ? $_POST['G_num'] : [];

    if ($courseID <= 0 || $term <= 0 || empty($scores)) {
        $_SESSION["send_error"] = true;
        header("Location:add_score.php");
        exit();
    }

    foreach ($scores as $studentID => $scoreValue) {
        if ($scoreValue !== '' && $scoreValue !== null) {
            if (!is_numeric($scoreValue)) {
                $_SESSION["score_error"] = true;
                header("Location:add_score.php");
                exit();
            }

            $numScore = floatval($scoreValue);
            if ($numScore < 0 || $numScore > 20) {
                $_SESSION["score_error"] = true;
                header("Location:add_score.php");
                exit();
            }
        }
    }

    try {
        $stmt_course = $connect->prepare("SELECT Co_type FROM courses WHERE Co_ID = :course_id LIMIT 1");
        $stmt_course->execute([':course_id' => $courseID]);
        $courseData = $stmt_course->fetch(PDO::FETCH_ASSOC);

        if (!$courseData) {
            $_SESSION["send_error"] = true;
            header("Location:add_score.php");
            exit();
        }

        $gType = intval($courseData['Co_type']);

        $connect->beginTransaction();

        $checkStmt = $connect->prepare("SELECT G_ID FROM grades WHERE G_StudentID = :student_id AND G_CourseID = :course_id AND G_Term = :term LIMIT 1");
        $updateStmt = $connect->prepare("UPDATE grades SET G_Num = :score, G_Type = :g_type WHERE G_ID = :g_id");
        $insertStmt = $connect->prepare("INSERT INTO grades (G_Num, G_StudentID, G_CourseID, G_Type, G_Term) VALUES (:score, :student_id, :course_id, :g_type, :term)");

        foreach ($scores as $studentID => $scoreValue) {
            $studentID = intval($studentID);

            if ($scoreValue === '' || $scoreValue === null) {
                $scoreValue = 0;
            } else {
                $scoreValue = floatval($scoreValue);
            }

            $checkStmt->execute([
                ':student_id' => $studentID,
                ':course_id' => $courseID,
                ':term' => $term
            ]);
            $existingGrade = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingGrade) {
                $updateStmt->execute([
                    ':score' => $scoreValue,
                    ':g_type' => $gType,
                    ':g_id' => $existingGrade['G_ID']
                ]);
            } else {
                $insertStmt->execute([
                    ':score' => $scoreValue,
                    ':student_id' => $studentID,
                    ':course_id' => $courseID,
                    ':g_type' => $gType,
                    ':term' => $term
                ]);
            }
        }

        $connect->commit();
        $_SESSION["add_score"] = true;
        header("Location:add_score.php");
        exit();

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        $_SESSION["send_error"] = true;
        header("Location:add_score.php");
        exit();
    }

} else {
    $_SESSION["send_error"] = true;
    header("Location:add_score.php");
    exit();
}
