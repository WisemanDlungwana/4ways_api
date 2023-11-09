<?php

error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$response = array('error' => true, 'message' => 'An unknown error occurred');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['senderID'], $_POST['receiverID'], $_POST['message'])
    ) {
        $senderID = $_POST['senderID'];
        $receiverID = $_POST['receiverID'];
        $message = $_POST['message'];

       $imageURL = "";
if(!empty($_FILES['image'])) {
    $fileName = $_FILES['image']['name'];
    $fileTmpName  = $_FILES['image']['tmp_name'];
    $fileType = $_FILES['image']['type'];
    $fileExtension = strtolower(end(explode('.',$fileName)));
    $fileName = time().".".$fileExtension;
    $imagePath = "directchatsimages/".$fileName;
    
    if(move_uploaded_file($fileTmpName, $imagePath)) {
        $baseURL = 'https://' . $_SERVER['HTTP_HOST'] . '/mob_app/api/';
        $imageURL = $baseURL . $imagePath; // Prepend the base URL
    } else {
        $response = array('error' => true, 'message' => 'Failed to upload image');
        echo json_encode($response);
        exit();
    }
}


       $db_host = 'localhost';
        $db_name = 'waysprod_4way';
        $db_user = 'waysprod_wiseman';
        $db_pass = 'Christforme19';

        $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if (mysqli_connect_errno()) {
            $response = array('error' => true, 'message' => 'Database connection failed');
        } else {
            $query = "INSERT INTO directchat (senderID, receiverID, message, imageURL, timestamp) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ssss", $senderID, $receiverID, $message, $imageURL);

            if ($stmt->execute()) {
                $response = array('error' => false, 'message' => 'Direct message sent successfully');
            } else {
                $response = array('error' => true, 'message' => 'Failed to send direct message');
            }
            $stmt->close();
            $mysqli->close();
        }
    } else {
        $response = array('error' => true, 'message' => 'Invalid data in the request');
    }
} else {
    $response = array('error' => true, 'message' => 'Invalid request method');
}

echo json_encode($response);
?>
