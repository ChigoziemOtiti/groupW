<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once('../model/User.php');

$userModel = new User();

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Email is required to resend OTP"]);
    exit();
}

$result = $userModel->resendOTP($email);

if (isset($result['status']) && $result['status'] === 'success') {
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($result);
