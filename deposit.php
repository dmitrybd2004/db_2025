<?php
session_start();
try{
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $username = $_SESSION["username"];
        $amount = $_POST['amount'];

        if ($amount == '') {
            throw new Exception("Please enter the amount of funds");
        }


        $host = "localhost";
        $dbusername = "root";
        $dbpassword = "3215979361";
        $dbname = "auth";

        $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);

        if($conn->connect_error){
            die("Connetion failed: ". $conn->connect_error);
        }

        $query = "SELECT balance FROM user_info WHERE user_info.username='$username'";
        $result = $conn->query($query);
        
        $funds = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $old_balance = $funds[0]['balance'];
        $new_balance = $old_balance + $amount;

        $query = "UPDATE user_info SET balance = $new_balance WHERE user_info.username='$username'";
        $conn->query($query);

        header("Location: user_info.php?type=buy");
        exit();

        $conn->close();
    }
}
catch (Exception $e) {
    header("Location: user_info.php?type=buy&error=" . urlencode($e->getMessage()));
    exit();
}

?>