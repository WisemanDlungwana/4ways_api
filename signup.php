<?php

error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['name'], $_POST['surname'], $_POST['email'], $_POST['password']) &&
        !empty($_FILES['image_path'])
    ) {
        $first_name = $_POST['name'];
        $last_name = $_POST['surname'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Handle image upload
        $target_dir = "signupdocs/";
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
                        // Check if the agent exists
                        $check_query = "SELECT * FROM agent WHERE email = ?";
                        $check_stmt = $mysqli->prepare($check_query);
                        $check_stmt->bind_param("s", $email);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();

                        if ($check_result->num_rows > 0) {
                            // Check if the user exists
                            $check_query2 = "SELECT * FROM users WHERE email = ?";
                            $check_stmt = $mysqli->prepare($check_query2);
                            $check_stmt->bind_param("s", $email);
                            $check_stmt->execute();
                            $check_result2 = $check_stmt->get_result();
                            
                            if ($check_result2->num_rows > 0) {
                                $response = array('error' => true, 'message' => 'User already exist');
                            } else {
                                $query = "INSERT INTO users (first_name, last_name, email, password, image_path) VALUES (?, ?, ?, ?, ?)";
                                $stmt = $mysqli->prepare($query);
                                $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $imagePath);
                                if ($stmt->execute()) {
                                    $response = array('error' => false, 'message' => 'Registration successful');
                                } else {
                                    $response = array('error' => true, 'message' => 'Registration failed');
                                }
                                $stmt->close();
                            }
                        } else {
                            $response = array('error' => true, 'message' => 'You are not on the network');
                        }
                        $check_stmt->close();
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
