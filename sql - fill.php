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

$sql = "LOAD DATA INFILE 'lang.csv'
INTO TABLE DefinitionsTable
COLUMNS TERMINATED BY ','
LINES TERMINATED BY '\r\n'
IGNORE 1 ROWS";


if ($conn->query($sql) === TRUE) {
    echo "Loaded with shit successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>