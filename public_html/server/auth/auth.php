<?php
function register($db, $data)
{
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $errors = [];

    if (!$name) {
        $errors['name'] = 'Name is required';
    }
    if (!$email) {
        $errors['email'] = 'Email is required';
    }
    if (!$password) {
        $errors['password'] = 'Password is required';
    }
    if (!empty($errors)) {
        return ['status' => false, 'errors' => $errors];
    }
    $email_safe = mysqli_real_escape_string($db, $email);
    $sql = "SELECT id FROM users WHERE email = '$email_safe'";
    $res = mysqli_query($db, $sql);
    if (mysqli_num_rows($res) > 0) {
        $errors['email'] = 'Email already exists';
        return ['status' => false, 'errors' => $errors];
    }

    $name_safe = mysqli_real_escape_string($db, $name);
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
    $insert_sql = "INSERT INTO users (name, email, password,created_at) 
                   VALUES ('$name_safe', '$email_safe', '$password_hashed',NOW())";

    $insert_res = mysqli_query($db, $insert_sql);
    if ($insert_res) {
        return ['status' => true, 'success' => 'Registered successfully'];
    } else {
        return ['status' => false, 'errors' => ['database' => mysqli_error($db)]];
    }
}

function login($con, $data) {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $errors = [];

    if (!$email) {
        $errors['email'] = 'Email-ը պարտադիր է։';
    }
    if (!$password) {
        $errors['password'] = 'Գաղտնաբառը պարտադիր է։';
    }

    if (!empty($errors)) {
        return ['status' => false, 'errors' => $errors];
    }

    $email_safe = mysqli_real_escape_string($con, $email);
    $sql = "SELECT * FROM users WHERE email = '$email_safe' LIMIT 1";
    $result = mysqli_query($con, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            return ['status' => true, 'user' => $user];
        } else {
            $errors['password'] = 'Սխալ գաղտնաբառ։';
            return ['status' => false, 'errors' => $errors];
        }
    } else {
        $errors['email'] = 'Այդ email-ով օգտվող գոյություն չունի։';
        return ['status' => false, 'errors' => $errors];
    }
}

function logout() {
    session_start();
    if (isset($_SESSION['user'])) {
        unset($_SESSION['user']);
    }
    session_destroy();
}
