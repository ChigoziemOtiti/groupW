<?php
include_once('../db/Connection.php');
include_once('../trait/BasicOperation.php');
include_once('../trait/Validate.php');

class Student extends Connection
{
    use BasicOperation, Validation;

    public function __construct()
    {
        parent::__construct();
    }

    public function RegisterStudent(
        string $first_name,
        string $last_name,
        string $middle_name,
        string $gender,
        string $matric_no,
        string $email,
        string $password,
        $dept_id,
        $fac_id
    ) {
        $validationResult = $this->ValidateStudentRegistration($first_name, $last_name, $gender, $matric_no, $email, $password, $dept_id, $fac_id);

        if ($validationResult !== "valid") {
            return ["status" => "error", "message" => $validationResult];
        }
        //check if email already exist
        $emailExists = $this->recordExists('Students', 'email', $email);
        if ($emailExists) {
            return ["status" => "error", "message" => "A student with this email already exists"];
        }
        //check if matric number already exist
        $matricExists = $this->recordExists('Students', 'matric_no', $matric_no);
        if ($matricExists) {
            return ["status" => "error", "message" => "This matriculation number is already registered"];
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insertStatus = $this->insertStudent($first_name, $last_name, $middle_name, $gender, $matric_no, $email, $hashed_password, $dept_id, $fac_id);

        if ($insertStatus) {
            return ["status" => "success", "message" => "Student registered successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to register student due to a database error"];
        }
    }
}
