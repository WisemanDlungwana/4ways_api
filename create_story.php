<?php

error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$response = array('error' => true, 'message' => 'An unknown error occurred');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['name'], $_POST['surname'], $_POST['post']) &&
        !empty($_FILES['image_path'])
    ) {
        $first_name = $_POST['name'];
        $last_name = $_POST['surname'];
        $content = $_POST['post'];

        // Handle image upload
        $target_dir = "storiesfiles/";
        $original_file_name = basename($_FILES['image_path']['name']);
        $target_file = $target_dir . $original_file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file already exists and rename if needed
        $counter = 0;
        while (file_exists($target_file)) {
            $counter++;
            $target_file = $target_dir . pathinfo($original_file_name, PATHINFO_FILENAME) . "_$counter." . $imageFileType;
        }

        // Check if the file is an actual image
        $check = getimagesize($_FILES['image_path']['tmp_name']);
        if ($check === false) {
            $response = array('error' => true, 'message' => 'Invalid image file');
        } else {
            if ($_FILES['image_path']['size'] > 500000 || !in_array($imageFileType, array("jpg", "jpeg", "png", "gif"))) {
                $response = array('error' => true, 'message' => 'Invalid image format or size');
            } else {
                if (move_uploaded_file($_FILES['image_path']['tmp_name'], $target_file)) {
                    $imagePath = $target_file;

                     $db_host = 'localhost';
                    $db_name = 'waysprod_4way';
                    $db_user = 'waysprod_wiseman';
                    $db_pass = 'Christforme19';

                    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

                    if (mysqli_connect_errno()) {
                        $response = array('error' => true, 'message' => 'Database connection failed');
                    } else {
                        $user_id = $_POST['user_id']; // Assuming you provide this in the POST request
                        
                        $expiryDate = new DateTime();  // Current date/time
                        $expiryDate->modify('+1 day');  // Add 24 hours

                        $query = "INSERT INTO stories (users_id, first_name, last_name, content, story_image, story_date, expiry_date) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
                        $stmt = $mysqli->prepare($query);
                        $stmt->bind_param("isssss", $user_id, $first_name, $last_name, $content, $imagePath, $expiryDate->format('Y-m-d H:i:s'));

                        if ($stmt->execute()) {
                            $response = array('error' => false, 'message' => 'Story added successfully');
                        } else {
                            $response = array('error' => true, 'message' => 'Failed to add story');
                        }

                        $stmt->close();
                        $mysqli->close();
                    }
                } else {
                    $response = array('error' => true, 'message' => 'Image upload failed');
                }
            }
        }
    } else {
        $response = array('error' => true, 'message' => 'Invalid data in the request');
    }
} else {
    $response = array('error' => true, 'message' => 'Invalid request method');
}

echo json_encode($response);

?>
