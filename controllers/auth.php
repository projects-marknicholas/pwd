<?php
class AuthController{
  public function register() {
    global $conn;
    date_default_timezone_set('Asia/Manila');
    $response = array();

    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = bin2hex(random_bytes(16));
    $first_name = htmlspecialchars($data['first_name'] ?? '');
    $middle_name = htmlspecialchars($data['middle_name'] ?? '');
    $last_name = htmlspecialchars($data['last_name'] ?? '');
    $email = htmlspecialchars($data['email'] ?? '');
    $password = htmlspecialchars($data['password'] ?? '');
    $confirm_password = htmlspecialchars($data['confirm_password'] ?? '');
    $role = htmlspecialchars($data['role'] ?? 'pending');
    $status = "pending";
    $created_at = date('Y-m-d H:i:s');$created_at = date('Y-m-d H:i:s');

    if (empty($first_name) || empty($middle_name) || empty($last_name)) {
      $response['status'] = 'error';
      $response['message'] = 'Name fields cannot be empty';
      echo json_encode($response);
      return;
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $response['status'] = 'error';
      $response['message'] = 'Invalid email format';
      echo json_encode($response);
      return;
    }
  
    if (empty($password) || strlen($password) < 6 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
      $response['status'] = 'error';
      $response['message'] = 'Password must be at least 6 characters long, contain an uppercase letter, a lowercase letter, and a number';
      echo json_encode($response);
      return;
    }
  
    if ($password !== $confirm_password) {
      $response['status'] = 'error';
      $response['message'] = 'Passwords do not match';
      echo json_encode($response);
      return;
    }

    $allowed_roles = ['user', 'employer', 'admin'];
    if (!in_array($role, $allowed_roles)) {
        throw new Exception('Invalid role specified');
    }

    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
      $stmt->close();
      $response['status'] = 'error';
      $response['message'] = 'This user already exists';
      echo json_encode($response);
      return;
    }
    $stmt->close();
  
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert data
    $stmt = $conn->prepare("INSERT INTO users (user_id, first_name, middle_name, last_name, email, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssssss', $user_id, $first_name, $middle_name, $last_name, $email, $hashed_password, $role, $status, $created_at);
  
    if ($stmt->execute()) {
      $response['status'] = 'success';
      $response['message'] = 'User created successfully';
      echo json_encode($response);
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Error creating user: ' . $conn->error;
      echo json_encode($response);
    }
  }

  public function login() {
    global $conn;
    date_default_timezone_set('Asia/Manila');
    $response = array();
  
    $data = json_decode(file_get_contents("php://input"), true);
    $email = htmlspecialchars(isset($data['email']) ? $data['email'] : '');
    $password = htmlspecialchars(isset($data['password']) ? $data['password'] : '');
    $created_at = date('Y-m-d H:i:s');

    if (empty($email)) {
      $response['status'] = 'error';
      $response['message'] = 'Email cannot be empty';
      echo json_encode($response);
      return;
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $response['status'] = 'error';
      $response['message'] = 'Invalid email format';
      echo json_encode($response);
      return;
    }
  
    if (empty($password)) {
      $response['status'] = 'error';
      $response['message'] = 'Password cannot be empty';
      echo json_encode($response);
      return;
    }

    // Check if user details are correct
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
  
    if ($result->num_rows === 0) {
      $response['status'] = 'error';
      $response['message'] = 'Email or password is incorrect';
      echo json_encode($response);
      return;
    }
  
    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
      $response['status'] = 'error';
      $response['message'] = 'Invalid email or password.';
      echo json_encode($response);
      return;
    } else {
      // if ($user['status'] === 'pending'){
      //   $response['status'] = 'error';
      //   $response['message'] = 'Your account is not yet activated, please verify your email address.';
      //   echo json_encode($response);
      //   return;
      // }

      // Update the last_login field upon successful login
      $update_stmt = $conn->prepare("UPDATE users SET last_login = ? WHERE email = ?");
      $update_stmt->bind_param("ss", $created_at, $email);
      $update_stmt->execute();
      $update_stmt->close();

      // Build response
      $response['status'] = 'success';
      $response['message'] = 'Login successful.';
      $response['user'] = [
        'user_id' => $user['user_id'],
        'email' => $user['email'],
        'first_name' => ucwords(strtolower($user['first_name'])),
        'middle_name' => ucwords(strtolower($user['middle_name'])),
        'last_name' => ucwords(strtolower($user['last_name'])),
        'role' => $user['role'],
        'status' => $user['status'],
        'education' => $user['education'],
        'skills' => $user['skills'],
        'resume' => $user['resume'],
        'last_login' => $user['last_login'],
        'created_at' => $user['created_at']
      ];
      echo json_encode($response);
      return;
    }
  }
}
?>