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

// Assuming that the month_year is sent as a POST request
$month_year = $_POST['month_year'];

// SQL query to fetch group messages with associated user data
$sql = "SELECT 
    g.groupchatID, 
    g.senderID, 
    u.first_name as sender_first_name, 
    u.last_name as sender_last_name,
    g.textMessage, 
    g.imageURL,
    g.timestamp
FROM groupchats g
JOIN users u ON g.senderID = u.id
WHERE g.month_year = ?
ORDER BY g.timestamp ASC ;"; // Messages are ordered by the date they were created

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $month_year);
    $stmt->execute();
    $result = $stmt->get_result();

    $baseURL = 'https://' . $_SERVER['HTTP_HOST'] . '/mob_app/api/';
    $messages = array();
    
    while($row = $result->fetch_assoc()) {
        // Append the base URL to the image paths
        if ($row['imageURL'] != null) {
    $row['imageURL'] = $baseURL . $row['imageURL'];
}
        $messages[] = $row;
    }

    echo json_encode([
        'message' => count($messages) > 0 ? 'Data fetched successfully.' : 'No messages found.',
        'data' => $messages,
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
