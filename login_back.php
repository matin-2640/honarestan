<?php
session_start();

$username = $_POST['username'];

$password = $_POST['password'];

include('connect.php');

$sql = " select * from students where Stu_nationalCode = :username  and  Stu_nationalCode= :password  LIMIT 1";

$stmt = $connect->prepare($sql);

$stmt->bindParam(":username", $username, PDO::PARAM_STR);
$stmt->bindParam(":password", $password, PDO::PARAM_STR);

$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $_SESSION["type"] = 0;
    $_SESSION["state_login"] = true;
    $_SESSION["ID"] = $user["Stu_ID"];
    header("location:panel.php");
    exit();
} else {
    $sql = " select * from teachers where T_nationalCode = :username  and  T_password= :password  LIMIT 1";

    $stmt = $connect->prepare($sql);

    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
    $stmt->bindParam(":password", $password, PDO::PARAM_STR);

    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION["type"] = 1;
        $_SESSION["state_login"] = true;
        $_SESSION["ID"] = $user["T_ID"];
        header("location:teacher_panel.php");
        exit();
    } else {
        $sql = " select * from admin where Ad_nationalCode = :username  and  Ad_password= :password  LIMIT 1";

        $stmt = $connect->prepare($sql);

        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":password", $password, PDO::PARAM_STR);

        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION["state_login"] = true;
            $_SESSION["ID"] = $user["Ad_ID"];
            if ($user["type"] == 0) {
                $_SESSION["type"] = 2;
            } else if ($user["type"] == 1) {
                $_SESSION["type"] = 3;
            } else if ($user["type"] == 2) {
                $_SESSION["type"] = 4;
            }
            header("location:admin_panel.php");
            exit();
        } else {
            header("location:login.php");
            exit();
        }
    }
}
?>