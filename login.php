<?php

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
            header("Location: success.html");
            exit();
        }
        else{
            //Login failed
            header("Location: index.html");
            exit();
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
        $namequery = "SELECT * FROM login WHERE login.username='$username'";
        $passquery = "SELECT * FROM login WHERE login.password='$password' ";
        $nameresult = $conn->query($namequery);
        $passresult = $conn->query($passquery);

        if($nameresult->num_rows > 0){
            // Name is taken
            //$Sign_in_error = "username already taken";
            echo $Sign_in_error;
            header("Location: index.html");
            exit();
        }
        else if($passresult->num_rows > 0){
            // Password is taken
            header("Location: index.html");
            exit();
        }
        else{
            $insertquery = "INSERT INTO login (username, password) VALUES('$username', '$password')";
            // $insertquery = "INSERT INTO login (username, password) VALUES($username, $password)";
            $conn->query($insertquery);
            header("Location: success.html");
            exit();
        }

        $conn->close();
    }
}
?>