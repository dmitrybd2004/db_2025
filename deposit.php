<?php
try{
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        //retrieve form data
        $username = htmlspecialchars($_GET['user']);
        $amount = $_POST['amount'];

        if ($amount == '') {
            throw new Exception("Please enter the amount of funds");
        }

        //Database connection

        $host = "localhost";
        $dbusername = "root";
        $dbpassword = "3215979361";
        $dbname = "auth";

        $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);

        if($conn->connect_error){
            die("Connetion failed: ". $conn->connect_error);
        }

        // validate login authentication
        $query = "SELECT balance FROM user_info WHERE user_info.username='$username'";
        $result = $conn->query($query);
        
        $funds = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $old_balance = $funds[0]['balance'];
        $new_balance = $old_balance + $amount;

        $query = "UPDATE user_info SET balance = $new_balance WHERE user_info.username='$username'";
        $conn->query($query);

        header("Location: user_info.php?user=" . $username);
        exit();

        $conn->close();
    }
}
catch (Exception $e) {
    // Redirect back to the form with an error message
    header("Location: user_info.php?user=" . $username . "&error=" . urlencode($e->getMessage()));
    exit();
}

?>