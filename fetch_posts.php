<?php
   $db_host = 'localhost';
    $db_name = 'waysprod_4way';
    $db_user = 'waysprod_wiseman';
    $db_pass = 'Christforme19';

    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if($mysqli->connect_error) {
        die(json_encode([
            'message' => 'Database connection failed.',
            'data' => [],
            'error' => true
        ]));
    }

    // Updated SQL query to fetch all posts with associated user data
    $sql = "SELECT 
    p.post_id, 
    p.users_id, 
    u.first_name as user_first_name, 
    u.image_path, 
    p.content, 
    p.post_image, 
    p.post_date,
    COALESCE(l.likes_count, 0) as likes
FROM posts p
JOIN users u ON p.users_id = u.id
LEFT JOIN (
    SELECT post_id, COUNT(*) as likes_count 
    FROM post_likes
    GROUP BY post_id
) l ON p.post_id = l.post_id
ORDER BY p.post_date DESC;"; // Added the ORDER BY clause

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->execute();
        $result = $stmt->get_result();

        $baseURL = 'https://' . $_SERVER['HTTP_HOST'] . '/mob_app/api/';
        $posts = array();
        
        while($row = $result->fetch_assoc()) {
            $row['post_image'] = $baseURL . $row['post_image'];
            $row['image_path'] = $baseURL . $row['image_path'];
            $posts[] = $row;
        }

        echo json_encode([
            'message' => count($posts) > 0 ? 'Data fetched successfully.' : 'No data found.',
            'data' => $posts,
            'error' => false
        ]);

        $stmt->close();
    } else {
        echo json_encode([
            'message' => 'Query preparation failed.',
            'data' => [],
            'error' => true
        ]);
    }

    $mysqli->close();
?>
