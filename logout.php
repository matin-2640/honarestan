<?php
session_start();
unset($_SESSION['type']);
unset($_SESSION['state_login']);
unset($_SESSION['ID']);
header('location:index.html');
?>