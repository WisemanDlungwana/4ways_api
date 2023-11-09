<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$response = array('error' => true, 'message' => 'An unknown error occurred');

   $db_host = 'localhost';
    $db_name = 'waysprod_4way';
    $db_user = 'waysprod_wiseman';
    $db_pass = 'Christforme19';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die(json_encode([
        'message' => 'Database connection failed.',
        'data' => [],
        'error' => true
    ]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userID'])) {
    $users_id = $_POST['userID'];

    // Current month and year format
    $current_month_year = date('F Y');

    $sql = "SELECT g.group_id, g.month_year
            FROM users u
            JOIN user_groups g ON u.groupID = g.group_id
            WHERE u.id = ? AND g.month_year = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("is", $users_id, $current_month_year);
        $stmt->execute();
        $result = $stmt->get_result();

        $groups = array();
        
        while ($row = $result->fetch_assoc()) {
            $groups[] = $row;
        }

        if (count($groups) == 0) {
            // Insert new group record if not found
            $insert_sql = "INSERT INTO user_groups (month_year, created_date) VALUES (?, NOW())";
            if ($insert_stmt = $mysqli->prepare($insert_sql)) {
                $insert_stmt->bind_param("s", $current_month_year);
                $insert_stmt->execute();
                
                $new_group_id = $mysqli->insert_id;
                $groups[] = ['group_id' => $new_group_id, 'month_year' => $current_month_year];

                $insert_stmt->close();
            }
        }

        echo json_encode([
            'message' => count($groups) > 0 ? 'Data fetched successfully.' : 'No groups found for user.',
            'data' => $groups,
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
} else {
    echo json_encode([
        'message' => 'Invalid request or missing user ID.',
        'data' => [],
        'error' => true
    ]);
}

$mysqli->close();
?>
