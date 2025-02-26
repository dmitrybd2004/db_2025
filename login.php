<?php
session_start();
try{
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_POST['login_btn'])){
            //retrieve form data
            $username = $_POST['username']; 
            $password = $_POST['password'];
    
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
            $query = "SELECT * FROM login WHERE login.username='$username' AND login.password='$password' ";
            $result = $conn->query($query);
    
            if($result->num_rows == 1){
                // Login success
                $_SESSION["username"] = $username;
                header("Location: success.php");
                exit();
            }
            else{
                //Login failed
                throw new Exception("Wrong username or password");
                // header("Location: index.html");
                // exit();
            }
    
            $conn->close();
        }
        else if(isset($_POST['sign_in_btn'])){
            //retrieve form data
            $username = $_POST['username']; 
            $password = $_POST['password'];
    
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

            if ($username == '') {
                throw new Exception("Please enter the username");
            }
            if ($password == '') {
                throw new Exception("Please enter the password");
            }

            $namequery = "SELECT * FROM login WHERE login.username='$username'";
            $passquery = "SELECT * FROM login WHERE login.password='$password'";
            $nameresult = $conn->query($namequery);
            $passresult = $conn->query($passquery);
            
            if($nameresult->num_rows > 0){
                throw new Exception("Username already taken");
            }
            else if($passresult->num_rows > 0){
                throw new Exception("Password already taken");
            }
            else{
                $insertquery = "INSERT INTO login (username, password) VALUES('$username', '$password')";
                $conn->query($insertquery);
                $insertquery = "INSERT INTO user_info (username, balance, rating, products_bought, products_sold) VALUES('$username', 0, 0, 0, 0)";
                $conn->query($insertquery);

                $_SESSION["username"] = $username;
                header("Location: success.php");
                exit();
            }
    
            $conn->close();
        }
    }
}
catch (Exception $e) {
    // Redirect back to the form with an error message
    header("Location: index.php?error=" . urlencode($e->getMessage()));
    exit();
}

?>