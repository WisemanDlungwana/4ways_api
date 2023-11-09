<?php

error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$response = array('error' => true, 'message' => 'An unknown error occurred');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['senderID'], $_POST['month_year'], $_POST['textMessage'])
    ) {
        $senderID = $_POST['senderID'];
        $month_year = $_POST['month_year'];
        $textMessage = $_POST['textMessage'];

        $imageURL = "";
        if(!empty($_FILES['image'])) {
            $fileName = $_FILES['image']['name'];
            $fileTmpName  = $_FILES['image']['tmp_name'];
            $fileType = $_FILES['image']['type'];
            $fileExtension = strtolower(end(explode('.',$fileName)));
            $fileName = time().".".$fileExtension;
            $imagePath = "groupchatsimages/".$fileName;
            
            if(move_uploaded_file($fileTmpName, $imagePath)) {
                $imageURL = $imagePath;
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
            $query = "INSERT INTO groupchats (senderID, month_year, textMessage, imageURL, timestamp) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("isss", $senderID, $month_year, $textMessage, $imageURL);

            if ($stmt->execute()) {
                $response = array('error' => false, 'message' => 'Message sent successfully');
            } else {
                $response = array('error' => true, 'message' => 'Failed to send message');
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
