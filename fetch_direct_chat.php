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

// Get senderID and receiverID from the POST request
$senderID = $_POST['senderID'];
$receiverID = $_POST['receiverID'];

// SQL query to fetch direct chat messages between two users
$sql = "SELECT 
    d.chatID, 
    d.senderID, 
    d.message, 
    d.imageURL,
    d.timestamp
FROM directchat d
WHERE (d.senderID = ? AND d.receiverID = ?) OR (d.receiverID = ? AND d.senderID = ?)
ORDER BY d.timestamp ASC;"; 

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ssss", $senderID, $receiverID, $senderID, $receiverID);
    $stmt->execute();
    $result = $stmt->get_result();

    $baseURL = 'https://' . $_SERVER['HTTP_HOST'] . '/mob_app/api/';
    $messages = array();
    
    while($row = $result->fetch_assoc()) {
        // Append the base URL to the image paths
        
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
