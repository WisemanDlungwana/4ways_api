<?php

error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$response = array('error' => true, 'message' => 'An unknown error occurred');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['storyuserid'], $_POST['loggeduserid'], $_POST['storyid'])
    ) {
        $story_id = $_POST['storyid'];
        $user_id = $_POST['loggeduserid'];

        $db_host = 'localhost';
        $db_name = 'waysprod_4way';
        $db_user = 'waysprod_wiseman';
        $db_pass = 'Christforme19';

        $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if (mysqli_connect_errno()) {
            $response = array('error' => true, 'message' => 'Database connection failed');
        } else {
            $query_check = "SELECT * FROM story_likes WHERE story_id = ? AND user_id = ?";
            $stmt_check = $mysqli->prepare($query_check);
            $stmt_check->bind_param("ii", $story_id, $user_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // If a like record already exists, we "unlike" the story by deleting the record
                $stmt_unlike = $mysqli->prepare("DELETE FROM story_likes WHERE story_id = ? AND user_id = ?");
                $stmt_unlike->bind_param("ii", $story_id, $user_id);
                $stmt_unlike->execute();
                
                $response = array('error' => false, 'message' => 'Story unliked successfully');
                $stmt_unlike->close();
            } else {
                // If no like record exists, we "like" the story by adding a new record
                $stmt_like = $mysqli->prepare("INSERT INTO story_likes (story_id, user_id) VALUES (?, ?)");
                $stmt_like->bind_param("ii", $story_id, $user_id);
                $stmt_like->execute();

                $response = array('error' => false, 'message' => 'Story liked successfully');
                $stmt_like->close();
            }

            $stmt_check->close();
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
