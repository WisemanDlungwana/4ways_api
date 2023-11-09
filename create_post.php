<?php

error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$response = array('error' => true, 'message' => 'An unknown error occurred');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name'], $_POST['surname'])) {
        $first_name = isset($_POST['name']) ? $_POST['name'] : "";
        $last_name = isset($_POST['surname']) ? $_POST['surname'] : "";
        $content = isset($_POST['post']) ? $_POST['post'] : "";

        // Handle image upload
        $imagePath = ""; // Initialize as empty

        if (!empty($_FILES['image_path']) ) {
    
            $target_dir = "postsfiles/";
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
            } elseif ($_FILES['image_path']['size'] > 500000 || !in_array($imageFileType, array("jpg", "jpeg", "png", "gif"))) {
                $response = array('error' => true, 'message' => 'Invalid image format or size');
            } elseif (!move_uploaded_file($_FILES['image_path']['tmp_name'], $target_file)) {
                $response = array('error' => true, 'message' => 'Image upload failed');
            } else {
                $imagePath = $target_file;
            }
        }

        if (!empty($content) || !empty($imagePath)) {
           $db_host = 'localhost';
                    $db_name = 'waysprod_4way';
                    $db_user = 'waysprod_wiseman';
                    $db_pass = 'Christforme19';

            $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

            if (mysqli_connect_errno()) {
                $response = array('error' => true, 'message' => 'Database connection failed');
            } else {
                $user_id = $_POST['user_id']; // Assuming you provide this in the POST request

                // Check if the post already exists
                $check_query = "SELECT post_id FROM posts WHERE users_id = ? AND content = ?";
                $stmt = $mysqli->prepare($check_query);
                $stmt->bind_param("is", $user_id, $content);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $response = array('error' => true, 'message' => 'Post already submitted');
                } else {
                    // Insert the post if it doesn't exist
                    $query = "INSERT INTO posts (users_id, first_name, last_name, content, post_image, post_date) VALUES (?, ?, ?, ?, ?, NOW())";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param("issss", $user_id, $first_name, $last_name, $content, $imagePath);

                    if ($stmt->execute()) {
                        $response = array('error' => false, 'message' => 'Post submitted');
                    } else {
                        $response = array('error' => true, 'message' => 'Failed to create post');
                    }
                }

                $stmt->close();
                $mysqli->close();
            }
        } else {
            $response = array('error' => true, 'message' => 'No post content or image provided');
        }
    } else {
        $response = array('error' => true, 'message' => 'Invalid data in the request');
    }
} else {
    $response = array('error' => true, 'message' => 'Invalid request method');
}

echo json_encode($response);
?>
