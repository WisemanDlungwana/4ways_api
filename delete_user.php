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

$userID = $_POST['userID'];

// SQL to delete user based on userID
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $userID);
$result = $stmt->execute();

if ($result) {
    $response = array('status' => 'success', 'message' => 'User deleted successfully');
} else {
    $response = array('status' => 'error', 'message' => 'Failed to delete user');
}

echo json_encode($response);

$stmt->close();
$mysqli->close();
?>
