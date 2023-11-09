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

    // Updated SQL query to fetch all stories with associated user data and only those stories that haven't expired
    $sql = "SELECT 
    s.story_id, 
    s.users_id, 
    u.first_name as user_first_name, 
    u.image_path, 
    s.content, 
    s.story_image, 
    s.story_date,
    COALESCE(l.likes_count, 0) as likes
    FROM stories s
    JOIN users u ON s.users_id = u.id
    LEFT JOIN (
        SELECT story_id, COUNT(*) as likes_count 
        FROM story_likes
        GROUP BY story_id
    ) l ON s.story_id = l.story_id
    WHERE s.expiry_date > NOW();";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->execute();
        $result = $stmt->get_result();

        $baseURL = 'https://' . $_SERVER['HTTP_HOST'] . '/mob_app/api/';
        $stories = array();
        
        while($row = $result->fetch_assoc()) {
            $row['story_image'] = $baseURL . $row['story_image'];
            $row['image_path'] = $baseURL . $row['image_path'];
            $stories[] = $row;
        }

        echo json_encode([
            'message' => count($stories) > 0 ? 'Data fetched successfully.' : 'No data found.',
            'data' => $stories,
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
