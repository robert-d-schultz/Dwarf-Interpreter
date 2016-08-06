<?php header('Content-Type: text/html; charset=utf-8');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "definitions";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "CREATE TABLE DefinitionsTable (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
english VARCHAR(30) NOT NULL,
dwarf VARCHAR(30) NOT NULL,
elf VARCHAR(30) NOT NULL,
goblin VARCHAR(30) NOT NULL,
human VARCHAR(30) NOT NULL,
disambig VARCHAR(30) NOT NULL,
pos VARCHAR(30) NOT NULL,
noun VARCHAR(30),
verb VARCHAR(30)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>