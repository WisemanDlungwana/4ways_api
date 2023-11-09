<?php
$db_host = 'localhost';
$db_name = 'waysprod_4way';
$db_user = 'waysprod_wiseman';
$db_pass = 'Christforme19';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
    $response = array('error' => true, 'message' => 'Database connection failed');
}

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    // Login successful
    $row = $result->fetch_assoc();
    $user = array(
        'status' => 'success',
        'message' => 'Login successful',
        'name' => $row['name'],
        'surname' => $row['surname'],
        'profile_image' => $row['profile_image_url']
    );
} else {
    // Login failed
    $user = array('status' => 'error', 'message' => 'Invalid email or password');
}

header('Content-Type: application/json');
echo json_encode($user);

$mysqli->close();
?>
