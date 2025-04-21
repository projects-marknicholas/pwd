<?php
class UserController{
  public function add_application(){
    global $conn;
    date_default_timezone_set('Asia/Manila');
    $response = array();

    $data = json_decode(file_get_contents("php://input"), true);
    $apply_id = bin2hex(random_bytes(16));
    $user_id = htmlspecialchars($_GET['user_id'] ?? '');
    $job_id = htmlspecialchars($_GET['job_id'] ?? '');
    $status = "applied";
    $created_at = date('Y-m-d H:i:s');

    if (empty($user_id)) {
      $response['status'] = 'error';
      $response['message'] = 'User ID cannot be empty';
      echo json_encode($response);
      return;
    }
  
    if (empty($job_id)) {
      $response['status'] = 'error';
      $response['message'] = 'Job ID cannot be empty';
      echo json_encode($response);
      return;
    }

    // Check if user exists
    $user_stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $user_stmt->bind_param('s', $user_id);
    $user_stmt->execute();
    $user_stmt->store_result();

    if ($user_stmt->num_rows === 0) {
      $response['status'] = 'error';
      $response['message'] = 'User not found';
      echo json_encode($response);
      return;
    }

    // Check if job exists
    $job_stmt = $conn->prepare("SELECT job_id FROM jobs WHERE job_id = ?");
    $job_stmt->bind_param('s', $job_id);
    $job_stmt->execute();
    $job_stmt->store_result();

    if ($job_stmt->num_rows === 0) {
      $response['status'] = 'error';
      $response['message'] = 'Job not found';
      echo json_encode($response);
      return;
    }

    // Check if user already applied for this job
    $check_stmt = $conn->prepare("SELECT apply_id FROM applications WHERE user_id = ? AND job_id = ?");
    $check_stmt->bind_param('ss', $user_id, $job_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
      $response['status'] = 'error';
      $response['message'] = 'You have already applied for this job';
      echo json_encode($response);
      return;
    }

    // Insert application
    $insert_stmt = $conn->prepare("INSERT INTO applications (apply_id, user_id, job_id, status, created_at) VALUES (?, ?, ?, ?, ?)");
    $insert_stmt->bind_param('sssss', $apply_id, $user_id, $job_id, $status, $created_at);

    if ($insert_stmt->execute()) {
      // Check if job exists and get job_title
      $job_stmt = $conn->prepare("SELECT job_title, company_name FROM jobs WHERE job_id = ?");
      $job_stmt->bind_param('s', $job_id);
      $job_stmt->execute();
      $job_result = $job_stmt->get_result();

      if ($job_result->num_rows === 0) {
        $response['status'] = 'error';
        $response['message'] = 'Job not found';
        echo json_encode($response);
        return;
      }

      $job_data = $job_result->fetch_assoc();
      $job_title = $job_data['job_title'];
      $company_name = $job_data['company_name'];

      // Insert notification
      $notification_id = bin2hex(random_bytes(16));
      $notification_title = "Application Submitted";
      $notification_description = "You have successfully applied to the job: $job_title in $company_name";

      $notif_stmt = $conn->prepare("INSERT INTO notifications (notification_id, user_id, job_id, notification_title, notification_description, created_at) VALUES (?, ?, ?, ?, ?, ?)");
      $notif_stmt->bind_param('ssssss', $notification_id, $user_id, $job_id, $notification_title, $notification_description, $created_at);
      $notif_stmt->execute();

      $response['status'] = 'success';
      $response['message'] = 'Application submitted successfully';
      echo json_encode($response);
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Error submitting application: ' . $conn->error;
      echo json_encode($response);
    }
  }

  public function display_application(){
    global $conn;
    $response = array();
  
    $user_id = htmlspecialchars($_GET['user_id'] ?? '');
  
    $query = "
      SELECT 
        a.apply_id, 
        a.user_id, 
        a.job_id, 
        a.status, 
        a.created_at,
        j.job_title, 
        j.job_description, 
        j.company_name, 
        j.rate
      FROM applications a
      JOIN jobs j ON a.job_id = j.job_id
    ";
  
    // If user_id is provided, filter results
    if (!empty($user_id)) {
      $query .= " WHERE a.user_id = ?";
      $stmt = $conn->prepare($query);
      $stmt->bind_param('s', $user_id);
    } else {
      $stmt = $conn->prepare($query);
    }
  
    if ($stmt->execute()) {
      $result = $stmt->get_result();
      $applications = array();
  
      while ($row = $result->fetch_assoc()) {
        $applications[] = array(
          'apply_id' => $row['apply_id'],
          'user_id' => $row['user_id'],
          'job_id' => $row['job_id'],
          'status' => $row['status'],
          'created_at' => $row['created_at'],
          'job_title' => $row['job_title'],
          'job_description' => $row['job_description'],
          'company_name' => $row['company_name'],
          'rate' => $row['rate']
        );
      }
  
      $response['status'] = 'success';
      $response['applications'] = $applications;
      echo json_encode($response);
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Failed to fetch applications';
      echo json_encode($response);
    }
  }  

  public function update_account(){
    global $conn;
    $response = array();
    
    $user_id = htmlspecialchars($_GET['user_id'] ?? '');
    $skills = htmlspecialchars($_POST['skills'] ?? '');
    $education = htmlspecialchars($_POST['education'] ?? '');
    
    if (empty($user_id)) {
      $response['status'] = 'error';
      $response['message'] = 'User ID is required';
      echo json_encode($response);
      return;
    }
  
    // Check if user exists
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $check_stmt->bind_param('s', $user_id);
    $check_stmt->execute();
    $check_stmt->store_result();
  
    if ($check_stmt->num_rows === 0) {
      $response['status'] = 'error';
      $response['message'] = 'User not found';
      echo json_encode($response);
      return;
    }
  
    // Handle resume file upload (if any)
    $resume_path = null;
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
      $upload_dir = './uploads/resume/';
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
      }
  
      $file_tmp = $_FILES['resume']['tmp_name'];
      $file_name = basename($_FILES['resume']['name']);
      $resume_path = $upload_dir . $file_name;
  
      $file_type = strtolower(pathinfo($resume_path, PATHINFO_EXTENSION));
      if ($file_type !== 'pdf') {
        $response['status'] = 'error';
        $response['message'] = 'Only PDF files are allowed for resumes';
        echo json_encode($response);
        return;
      }
  
      move_uploaded_file($file_tmp, $resume_path);
    }
  
    // Update query
    if ($resume_path) {
      $sql = "UPDATE users SET skills = ?, education = ?, resume = ? WHERE user_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('ssss', $skills, $education, $file_name, $user_id);
    } else {
      $sql = "UPDATE users SET skills = ?, education = ? WHERE user_id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('sss', $skills, $education, $user_id);
    }
  
    if ($stmt->execute()) {
      $response['status'] = 'success';
      $response['message'] = 'Account updated successfully';
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Failed to update account: ' . $conn->error;
    }
  
    echo json_encode($response);
  }  

  public function display_jobs() {
    global $conn;
    $response = array();
  
    $stmt = $conn->prepare("SELECT job_id, job_title, job_description, company_name, rate, created_at FROM jobs ORDER BY created_at DESC");
    
    if ($stmt->execute()) {
      $result = $stmt->get_result();
      $jobs = array();
      
      while ($row = $result->fetch_assoc()) {
        $jobs[] = array(
          'job_id' => $row['job_id'],
          'job_title' => $row['job_title'],
          'job_description' => $row['job_description'],
          'company_name' => $row['company_name'],
          'rate' => $row['rate'],
          'created_at' => $row['created_at']
        );
      }
      
      $response['status'] = 'success';
      $response['jobs'] = $jobs;
      echo json_encode($response);
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Error fetching jobs: ' . $conn->error;
      echo json_encode($response);
    }
  }
}
?>