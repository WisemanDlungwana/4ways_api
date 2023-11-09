<?php

$db_host = 'localhost';
$db_name = 'waysprod_4way';
$db_user = 'waysprod_wiseman';
$db_pass = 'Christforme19';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if (mysqli_connect_errno()) {
    $response = array('error' => true, 'message' => 'Database connection failed');
    echo json_encode($response);
    exit();
}

$email = $_POST['email'];

$sql = "SELECT id, first_name, last_name, image_path, role FROM users WHERE email = ? ";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($id, $first_name, $last_name, $image_path, $role);
$stmt->fetch();

if ($id) {
    $user = array(
        'status' => 'success',
        'message' => 'Data fetched successfully',
        'userID' => $id,
        'name' => $first_name,
        'surname' => $last_name,
        'profile_image_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/mob_app/api/' . $image_path,
         'role' => $role,
    );
    echo json_encode($user);
} else {
    $response = array('status' => 'error', 'message' => 'Invalid email or password');
    echo json_encode($response);
}

$stmt->close();
$mysqli->close();
?>
