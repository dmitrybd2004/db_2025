<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $item_id = $_POST['item_id'];

    $host = "localhost";
    $dbusername = "root";
    $dbpassword = "3215979361";
    $dbname = "auth";

    $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);

    if($conn->connect_error){
        die("Connetion failed: ". $conn->connect_error);
    }

    $query = "SELECT seller_name, buyer_name, item_name FROM lot WHERE item_id = $item_id";
    $result = $conn->query($query);
    $info = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $seller = $info[0]['seller_name'];
    $buyer = $info[0]['buyer_name'];
    $item_name = $info[0]['item_name'];
    $description = $_POST['user_review'];

    if(isset($_POST['refund_btn'])){

        $insertquery = "INSERT INTO refunds (item_id, item_name, description, report_type, sent_by) VALUES($item_id, '$item_name', '$description', 0, '$buyer')";
        $conn->query($insertquery);

    }
    if(isset($_POST['report_btn'])){
        $insertquery = "INSERT INTO refunds (item_id, item_name, description, report_type, sent_by) VALUES($item_id, '$item_name', '$description', 1, '$buyer')";
        $conn->query($insertquery);
    }

    header("Location:user_info.php?type=buy");
    exit();

    $conn->close();
}
?>