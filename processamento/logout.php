<?php

session_start();
require_once __DIR__ . '/../controller/AuthController.php';

$authController = new AuthController();
$authController->logout();

header('Location: ../view/login.php');
exit();
