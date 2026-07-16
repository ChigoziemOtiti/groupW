<?php
include_once("../db/Connection.php");
include_once("../trait/BasicOperation.php");
include_once("../trait/Validate.php");

class User extends Connection
{

    use BasicOperation, Validation;

    public function __construct()
    {
        parent::__construct();
    }

    public function SeedUser()
    {
        //seed user
        $name = 'john';
        $password = 'john123';

        //first check to know if user exist 
        $check =  $this->recordExists('users', 'first_name', $name);
        if ($check) {
            return ["status" => "error", "message" => "A user aleady exist try to login or cantact support"];
        }

        //hash password and get token 
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));
        //add user into db
        $seed_user = $this->InsertUser($name, 'doe', '', 'johndoe@gmail.com', $hash_password, 'Moderator', $token, 'true');

        if ($seed_user) {
            return ["status" => "success", "message" => "User seeded Successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to Seed User due to a database error"];
        }
    }

    // to add users
    public function AddUser(
        string $first_name,
        string $last_name,
        string $middle_name,
        string $email,
        string $password,
        string $role,
        string $active
    ) {
        //validating users details
        $validationResult = $this->ValidateAddingUser($first_name, $last_name, $email, $password, $role, $active);

        if ($validationResult !== "valid") {
            return ["status" => "error", "message" => $validationResult];
        }

        //check if email already exist
        $emailExists = $this->recordExists('users', 'email', $email);
        if ($emailExists) {
            return ["status" => "error", "message" => "A student with this email already exists"];
        }

        //hashpassword
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // tokenise
        $token = bin2hex(random_bytes(32));


        $insertStatus =  $this->InsertUser($first_name, $last_name, $middle_name, $email, $hashed_password, $role, $token, $active);

        if ($insertStatus) {
            return ["status" => "success", "message" => "User Added  successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to Add Student due to a database error"];
        }
    }

    public function loginUser(
        string $email,
        string $password
    ) {
        // Validate first
        $isValid = $this->ValidateLogin($email, $password);

        if ($isValid !== "valid") {
            return ["status" => "error", "message" => $isValid];
        }

        $user_data = $this->getSingleRecord('users', 'email', $email);

        // Check if the user DOES NOT exist
        if (!$user_data) {
            return ["status" => "error", "message" => "User does not exist. Try registering first."];
        }

        // Verify password
        if (password_verify($password, $user_data['password'])) {
            return [
                "status" => "success",
                "message" => "Login successful",
                "token" => $user_data['tokens']
            ];
        } else {
            return ["status" => "error", "message" => "Incorrect password"];
        }
    }

    // to get all users
    public function GetUser()
    {
        return  $this->getAllRecords('users');
    }
}
