<?php
session_start();
try{
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_POST['login_btn'])){
            $username = $_POST['username']; 
            $password = $_POST['password'];
    
            $host = "localhost";
            $dbusername = "root";
            $dbpassword = "3215979361";
            $dbname = "auth";
    
            $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);
    
            if($conn->connect_error){
                die("Connetion failed: ". $conn->connect_error);
    
            }
            $query = "SELECT * FROM login WHERE login.username='$username' AND login.password='$password' ";
            $result = $conn->query($query);
    
            if($result->num_rows == 1){
                $_SESSION["username"] = $username;

                $query = "SELECT * FROM login WHERE login.username='$username' AND login.banned=1";
                $result = $conn->query($query);
                if ($result->num_rows > 0) {
                    throw new Exception("Profile have been banned");
                }

                $query = "SELECT * FROM roles WHERE roles.username='$username' AND roles.role='moderator'";
                $result = $conn->query($query);
            
                if ($result->num_rows == 0) {
                    header("Location: home.php");
                    exit();
                }
                else{
                    header("Location: home_mod.php");
                    exit();
                }
            }
            else{
                throw new Exception("Wrong username or password");
            }
    
            $conn->close();
        }
        else if(isset($_POST['sign_in_btn'])){
            $username = $_POST['username']; 
            $password = $_POST['password'];
    
            $host = "localhost";
            $dbusername = "root";
            $dbpassword = "3215979361";
            $dbname = "auth";
    
            $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);
    
            if($conn->connect_error){
                die("Connetion failed: ". $conn->connect_error);
    
            }

            if ($username == '') {
                throw new Exception("Please enter the username");
            }
            if ($password == '') {
                throw new Exception("Please enter the password");
            }
            if (strlen($username) > 16) {
                throw new Exception("Username is too long (more than 16 symbols)");
            }
            if (strlen($password) > 16) {
                throw new Exception("Password is too long (more than 16 symbols)");
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
                $insertquery = "INSERT INTO roles (username) VALUES('$username')";
                $conn->query($insertquery);
                $_SESSION["username"] = $username;
                header("Location: home.php");
                exit();
            }
    
            $conn->close();
        }
    }
}
catch (Exception $e) {
    header("Location: login_page.php?error=" . urlencode($e->getMessage()));
    exit();
}

?>