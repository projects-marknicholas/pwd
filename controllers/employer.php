<?php
class EmployerController{
  public function add_job() {
    global $conn;
    date_default_timezone_set('Asia/Manila');
    $response = array();

    $data = json_decode(file_get_contents("php://input"), true);
    $job_id = bin2hex(random_bytes(16));
    $user_id = htmlspecialchars($_GET['user_id'] ?? '');
    $job_title = htmlspecialchars($data['job_title'] ?? '');
    $job_description = htmlspecialchars($data['job_description'] ?? '');
    $company_name = htmlspecialchars($data['company_name'] ?? '');
    $rate = htmlspecialchars($data['rate'] ?? '');
    $created_at = date('Y-m-d H:i:s');

    if (empty($job_title)) {
      $response['status'] = 'error';
      $response['message'] = 'Job Title cannot be empty';
      echo json_encode($response);
      return;
    }

    if (empty($job_description)) {
      $response['status'] = 'error';
      $response['message'] = 'Job Description cannot be empty';
      echo json_encode($response);
      return;
    }

    if (empty($company_name)) {
      $response['status'] = 'error';
      $response['message'] = 'Company Name cannot be empty';
      echo json_encode($response);
      return;
    }

    if (empty($rate)) {
      $response['status'] = 'error';
      $response['message'] = 'Rate cannot be empty';
      echo json_encode($response);
      return;
    }

    // Insert data
    $stmt = $conn->prepare("INSERT INTO jobs (user_id, job_id, job_title, job_description, company_name, rate, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssss', $user_id, $job_id, $job_title, $job_description, $company_name, $rate, $created_at);
  
    if ($stmt->execute()) {
      $response['status'] = 'success';
      $response['message'] = 'Job created successfully';
      echo json_encode($response);
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Error creating job: ' . $conn->error;
      echo json_encode($response);
    }
  }

  public function edit_job() {
    global $conn;
    date_default_timezone_set('Asia/Manila');
    $response = array();

    // Get data from the request body
    $data = json_decode(file_get_contents("php://input"), true);
    $job_id = htmlspecialchars($data['job_id'] ?? '');
    $user_id = htmlspecialchars($_GET['user_id'] ?? ''); // Access user_id from query string
    $job_title = htmlspecialchars($data['job_title'] ?? '');
    $job_description = htmlspecialchars($data['job_description'] ?? '');
    $company_name = htmlspecialchars($data['company_name'] ?? '');
    $rate = htmlspecialchars($data['rate'] ?? '');
    $updated_at = date('Y-m-d H:i:s');

    if (empty($job_id)) {
      $response['status'] = 'error';
      $response['message'] = 'Job ID is required';
      echo json_encode($response);
      return;
    }

    if (empty($job_title)) {
      $response['status'] = 'error';
      $response['message'] = 'Job Title cannot be empty';
      echo json_encode($response);
      return;
    }

    if (empty($job_description)) {
      $response['status'] = 'error';
      $response['message'] = 'Job Description cannot be empty';
      echo json_encode($response);
      return;
    }

    if (empty($company_name)) {
      $response['status'] = 'error';
      $response['message'] = 'Company Name cannot be empty';
      echo json_encode($response);
      return;
    }

    if (empty($rate)) {
      $response['status'] = 'error';
      $response['message'] = 'Rate cannot be empty';
      echo json_encode($response);
      return;
    }

    // Check if job exists
    $stmt = $conn->prepare("SELECT * FROM jobs WHERE job_id = ?");
    $stmt->bind_param('s', $job_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      $response['status'] = 'error';
      $response['message'] = 'Job not found';
      echo json_encode($response);
      return;
    }

    // Check if the user_id matches (optional but recommended for security)
    $job = $result->fetch_assoc();
    if ($job['user_id'] !== $user_id) {
      $response['status'] = 'error';
      $response['message'] = 'User not authorized to edit this job';
      echo json_encode($response);
      return;
    }

    // Update job data
    $stmt = $conn->prepare("UPDATE jobs SET job_title = ?, job_description = ?, company_name = ?, rate = ? WHERE job_id = ?");
    $stmt->bind_param('sssss', $job_title, $job_description, $company_name, $rate, $job_id);

    if ($stmt->execute()) {
      $response['status'] = 'success';
      $response['message'] = 'Job updated successfully';
      echo json_encode($response);
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Error updating job: ' . $conn->error;
      echo json_encode($response);
    }
  }

  public function display_jobs() {
    global $conn;
    $response = array();
  
    $user_id = htmlspecialchars($_GET['user_id'] ?? '');
  
    if (empty($user_id)) {
      $response['status'] = 'error';
      $response['message'] = 'User ID is required';
      echo json_encode($response);
      return;
    }
  
    $stmt = $conn->prepare("SELECT user_id, job_id, job_title, job_description, company_name, rate, created_at FROM jobs WHERE user_id = ? ORDER BY created_at DESC");
  
    if (!$stmt) {
      $response['status'] = 'error';
      $response['message'] = 'Prepare failed: ' . $conn->error;
      echo json_encode($response);
      return;
    }
  
    $stmt->bind_param('s', $user_id); 
  
    if ($stmt->execute()) {
      $result = $stmt->get_result();
      $jobs = array();
  
      while ($row = $result->fetch_assoc()) {
        $jobs[] = array(
          'user_id' => $row['user_id'],
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
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Error executing query: ' . $stmt->error;
    }
  
    echo json_encode($response);
  }  

  public function delete_job() {
    global $conn;
    $response = array();
    
    $data = json_decode(file_get_contents("php://input"), true);
    $job_id = htmlspecialchars($data['job_id'] ?? '');

    if (empty($job_id)) {
      $response['status'] = 'error';
      $response['message'] = 'Job ID cannot be empty';
      echo json_encode($response);
      return;
    }

    $check_stmt = $conn->prepare("SELECT job_id FROM jobs WHERE job_id = ?");
    $check_stmt->bind_param('s', $job_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows === 0) {
      $response['status'] = 'error';
      $response['message'] = 'Job not found';
      echo json_encode($response);
      return;
    }

    $delete_stmt = $conn->prepare("DELETE FROM jobs WHERE job_id = ?");
    $delete_stmt->bind_param('s', $job_id);

    if ($delete_stmt->execute()) {
      $response['status'] = 'success';
      $response['message'] = 'Job deleted successfully';
      echo json_encode($response);
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Error deleting job: ' . $conn->error;
      echo json_encode($response);
    }
  }

  public function display_application() {
    global $conn;
    $response = array();
  
    $user_id = htmlspecialchars($_GET['user_id'] ?? '');
  
    if (empty($user_id)) {
      $response['status'] = 'error';
      $response['message'] = 'User ID is required';
      echo json_encode($response);
      return;
    }
  
    $stmt = $conn->prepare("SELECT job_id FROM jobs WHERE user_id = ?");
    if (!$stmt) {
      $response['status'] = 'error';
      $response['message'] = 'Prepare failed for job query: ' . $conn->error;
      echo json_encode($response);
      return;
    }
  
    $stmt->bind_param('s', $user_id);
  
    if (!$stmt->execute()) {
      $response['status'] = 'error';
      $response['message'] = 'Error executing job query: ' . $stmt->error;
      echo json_encode($response);
      return;
    }
  
    $result = $stmt->get_result();
    $job_ids = array();
  
    while ($row = $result->fetch_assoc()) {
      $job_ids[] = $row['job_id'];
    }
  
    if (empty($job_ids)) {
      $response['status'] = 'error';
      $response['message'] = 'No jobs found for this employer';
      echo json_encode($response);
      return;
    }
  
    $placeholders = implode(',', array_fill(0, count($job_ids), '?'));
    $types = str_repeat('s', count($job_ids));
    
    $stmt = $conn->prepare("SELECT * FROM applications WHERE job_id IN ($placeholders)");
    if (!$stmt) {
      $response['status'] = 'error';
      $response['message'] = 'Prepare failed for applications query: ' . $conn->error;
      echo json_encode($response);
      return;
    }
  
    $stmt->bind_param($types, ...$job_ids);
  
    if ($stmt->execute()) {
      $result = $stmt->get_result();
      $applications = array();
  
      while ($row = $result->fetch_assoc()) {
        $user_stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, education, skills, resume FROM users WHERE user_id = ?");
        $user_stmt->bind_param('s', $row['user_id']);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        $user_stmt->close();
  
        $job_stmt = $conn->prepare("SELECT job_title FROM jobs WHERE job_id = ?");
        $job_stmt->bind_param('s', $row['job_id']);
        $job_stmt->execute();
        $job_result = $job_stmt->get_result();
        $job_data = $job_result->fetch_assoc();
        $job_stmt->close();
  
        $applications[] = array_merge($row, $user_data, $job_data);
      }
  
      $response['status'] = 'success';
      $response['applications'] = $applications;
      $response['job_ids'] = $job_ids;
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Error executing applications query: ' . $stmt->error;
    }
  
    echo json_encode($response);
  }

  public function update_application_status() {
    global $conn;
    $response = array();
  
    $apply_id = htmlspecialchars($_GET['apply_id'] ?? '');
    $new_status = htmlspecialchars($_GET['status'] ?? '');
    $valid_statuses = ['accepted', 'declined'];
  
    if (empty($apply_id)) {
      $response['status'] = 'error';
      $response['message'] = 'Application ID is required';
      echo json_encode($response);
      return;
    }
  
    if (!in_array($new_status, $valid_statuses)) {
      $response['status'] = 'error';
      $response['message'] = 'Invalid status value';
      echo json_encode($response);
      return;
    }
  
    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE apply_id = ?");
    if (!$stmt) {
      $response['status'] = 'error';
      $response['message'] = 'Prepare failed: ' . $conn->error;
      echo json_encode($response);
      return;
    }
  
    $stmt->bind_param('ss', $new_status, $apply_id);
  
    if ($stmt->execute()) {
      if ($stmt->affected_rows > 0) {
        $response['status'] = 'success';
        $response['message'] = 'Application status updated successfully';
      } else {
        $response['status'] = 'error';
        $response['message'] = 'No application found with that ID';
      }
    } else {
      $response['status'] = 'error';
      $response['message'] = 'Error updating application: ' . $stmt->error;
    }
  
    $stmt->close();
    echo json_encode($response);
  }
}
?>