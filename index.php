<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Configuration
$host = 'hostname';
$user = 'userid';
$password = 'password';
$database = 'database';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$textDisplay = '';
$nameDisplay = '';
$name = '';

$formSubmitted = isset($_SESSION['formSubmitted']);

unset($_SESSION['formSubmitted']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newText = $_POST['displayText'];
    $nameDisplay = $_POST['displayName'];

    // Update text_display
    $stmt = $conn->prepare("UPDATE u776728244_project.text_display SET text_display = ? WHERE id = 1");
    $stmt->bind_param('s', $newText);
    $stmt->execute();

    // Insert recent message
    $stmt = $conn->prepare("INSERT INTO u776728244_project.recent_messages (message, name) VALUES (?, ?)");
    $stmt->bind_param('ss', $newText, $nameDisplay);
    $stmt->execute();

    $_SESSION['formSubmitted'] = true;

    // Redirect to avoid form resubmission on page refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();

}

// Fetch text_display from the database
$result = $conn->query("SELECT name FROM u776728244_project.recent_messages WHERE id = (SELECT MAX(id) FROM u776728244_project.recent_messages);");
if ($result) {
    $row = $result->fetch_assoc();
    $name = $row['name'];
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Send a message!</title>
    <style>
        /* main.css */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .content {

            text-align: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 100%;
        }

        h1 {
            margin-bottom: 20px;
        }

        input {
            padding: 10px;
            width: 80%;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="content">
        <?php if ($formSubmitted) { ?>
            <h1>Thank you, <?= htmlspecialchars($name) ?>!</h1>
        <?php } else { ?>
            <h1>Hello</h1>
            <h1>Send a message to display on my screen anytime</h1>
        <?php } ?>

        <div class="form">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                <input type="text" name="displayText" placeholder="Add text to be displayed" maxlength="100" required />
                <input type="text" name="displayName" placeholder="Your name" maxlength="20" required />
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
</body>

</html>
