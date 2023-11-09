<?php
   $db_host = 'localhost';
$db_name = 'waysprod_4way';
$db_user = 'waysprod_wiseman';
$db_pass = 'Christforme19';

    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if (mysqli_connect_errno()) {
        die(json_encode(['error' => true, 'message' => 'Database connection failed']));
    }

    $userID = $_POST['userID'];

    $sql = "SELECT 
    CASE 
        WHEN d.senderID = ? THEN d.receiverID 
        ELSE d.senderID 
    END AS chatUserID,
    u.first_name,
    u.last_name,
    d.message AS last_message,
    d.timestamp AS last_timestamp
FROM 
    directchat d
JOIN 
    users u ON (d.senderID = u.id OR d.receiverID = u.id) AND u.id != ?
WHERE 
    (d.senderID = ? OR d.receiverID = ?)
    AND d.timestamp = (
        SELECT 
            MAX(timestamp)
        FROM 
            directchat
        WHERE
            (senderID = d.senderID AND receiverID = d.receiverID) 
            OR 
            (receiverID = d.senderID AND senderID = d.receiverID)
    )
ORDER BY 
    last_timestamp DESC";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ssss", $userID, $userID, $userID, $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        $baseURL = 'https://' . $_SERVER['HTTP_HOST'] . '/mob_app/api/';
        $chats = [];

        while($row = $result->fetch_assoc()) {
            $row['image_path'] = $baseURL . $row['image_path'];
            $chats[] = $row;
        }

        echo json_encode([
            'status' => count($chats) > 0 ? 'success' : 'error',
            'message' => count($chats) > 0 ? 'Data fetched successfully' : 'No chats found',
            'data' => $chats
        ]);

        $stmt->close();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Query preparation failed',
            'data' => []
        ]);
    }

    $mysqli->close();
?>
