<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
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

    $query = "SELECT seller_name, positive_review FROM lot WHERE item_id = $item_id";
    $result = $conn->query($query);
    $info = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $seller = $info[0]['seller_name'];
    $positive = $info[0]['positive_review'];

    if($positive == 0){

        $query = "UPDATE user_info SET rating = rating + 1 WHERE username = '$seller'";
        $conn->query($query);

        $query = "UPDATE lot SET positive_review = 1 WHERE item_id = $item_id";
        $conn->query($query);       
    }
    else if($positive == 1){

        $query = "UPDATE user_info SET rating = rating - 1 WHERE username = '$seller'";
        $conn->query($query);

        $query = "UPDATE lot SET positive_review = 0 WHERE item_id = $item_id";
        $conn->query($query);       
    }

    header("Location:user_info.php");
    exit();

    $conn->close();
}
?>