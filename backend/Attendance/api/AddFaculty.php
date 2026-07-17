<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once('../model/Faculty.php');

$facultyModel = new Faculty();

$name = isset($_POST['facultyname']) ? trim($_POST['facultyname']) : '';

if (empty($name)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faculty name cannot be empty"]);
    exit();
}



$result = $facultyModel->AddFaculty($name);
if ($result) {
    http_response_code(201);
    echo json_encode(["status" => "success", "message" => "Faculty added successfully"]);
} else {
    http_response_code(409);
    echo json_encode(["status" => "error", "message" => "Faculty already exists"]);
}
