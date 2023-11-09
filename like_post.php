<?php

error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$response = array('error' => true, 'message' => 'An unknown error occurred');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['postuserid'], $_POST['loggeduserid'], $_POST['postid'])
    ) {
        $post_id = $_POST['postid'];
        $user_id = $_POST['loggeduserid'];

        $db_host = 'localhost';
        $db_name = 'waysprod_4way';
        $db_user = 'waysprod_wiseman';
        $db_pass = 'Christforme19';

        $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if (mysqli_connect_errno()) {
            $response = array('error' => true, 'message' => 'Database connection failed');
        } else {
            $query_check = "SELECT user_id FROM post_likes WHERE post_id = ? AND user_id = ?";
            $stmt_check = $mysqli->prepare($query_check);
            $stmt_check->bind_param("ii", $post_id, $user_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Check if the user who liked the post is the same as the logged-in user
                $row = $result_check->fetch_assoc();
                if ($row['user_id'] == $user_id) {
                    // If a like record by the same user exists, we "unlike" the post by deleting the record
                    $stmt_unlike = $mysqli->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
                    $stmt_unlike->bind_param("ii", $post_id, $user_id);
                    $stmt_unlike->execute();

                    $response = array('error' => false, 'message' => 'Post unliked successfully');
                    $stmt_unlike->close();
                } else {
                    $response = array('error' => true, 'message' => 'Another user has already liked this post.');
                }
            } else {
                // If no like record exists, we "like" the post by adding a new record
                $stmt_like = $mysqli->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
                $stmt_like->bind_param("ii", $post_id, $user_id);
                $stmt_like->execute();

                $response = array('error' => false, 'message' => 'Post liked successfully');
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
