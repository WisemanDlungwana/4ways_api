<?php
$db_host = "techwise.mysql.database.azure.com";
$db_user = "wiseman";
$db_pass = "Christforme#19";
$db_name = "4waysdb";

$con = mysqli_init();
mysqli_ssl_set($con, NULL, NULL, "{path to CA cert}", NULL, NULL);
mysqli_real_connect($con, $db_host, $db_user, $db_pass, $db_name, 3306, MYSQLI_CLIENT_SSL);

if (mysqli_connect_errno()) {
    $response = array('error' => true, 'message' => 'Database connection failed');
}

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
$result = $con->query($sql);

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

$con->close();
?>
