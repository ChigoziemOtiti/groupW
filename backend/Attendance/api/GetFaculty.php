<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

    exit();
}

include_once _('../model/Faculty.php');

$facultyModel = new Faculty();
$data = $facultyModel->getFaculty();

echo json_encode([
    "status" => "success",
    "data" => $data
]);
