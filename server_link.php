<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "u776728244_zsingh_proj";
$password = "Supersecretdatabase3";
$dbname = "u776728244_project";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// SQL query to fetch the string
$sql = "SELECT message, name FROM u776728244_project.recent_messages WHERE id = (SELECT MAX(id) FROM u776728244_project.recent_messages);";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Return the data as a JSON object
     echo json_encode($row);
} else {
    echo json_encode(["error" => "No results"]);
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn->close();
?>
