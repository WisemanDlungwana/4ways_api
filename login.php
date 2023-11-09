<?php
$con = mysqli_init();
mysqli_ssl_set($con,NULL,NULL, "{path to CA cert}", NULL, NULL);
mysqli_real_connect($conn, "techwise.mysql.database.azure.com", "wiseman", "Christforme#19", "4waysdb", 3306, MYSQLI_CLIENT_SSL);

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
