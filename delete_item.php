<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = $_POST['username'];
    $item_id = $_POST['item_id'];


    //Database connection

    $host = "localhost";
    $dbusername = "root";
    $dbpassword = "3215979361";
    $dbname = "auth";

    $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);

    if($conn->connect_error){
        die("Connetion failed: ". $conn->connect_error);
    }

    $query = "DELETE FROM lot WHERE item_id=$item_id";
    $conn->query($query);       

    header("Location:user_info.php?user=" . $name);
    exit();

    $conn->close();
}
?>