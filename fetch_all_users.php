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

$sql = "SELECT id, first_name, last_name, image_path,role FROM users";
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $row['profile_image_url'] = 'https://' . $_SERVER['HTTP_HOST'] . '/mob_app/api/' . $row['image_path'];
    unset($row['image_path']);  // Remove the original image_path since we have profile_image_url
    $users[] = $row;
}

if (!empty($users)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Data fetched successfully',
        'users' => $users
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No users found'
    ]);
}

$stmt->close();
$mysqli->close();
?>
