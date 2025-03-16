<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <style>
            body{
                background-color:rgb(244, 255, 183);
            }
            .top-bar {
                background: black;
                color: white;
                padding: 10px;
                display: flex;
                justify-content: flex-start;
                align-items: center;
                gap: 15px;
            }
            .top-left {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .logout-btn {
                margin-left: auto;
            }
            .form-container {
                background-color: rgb(137, 191, 249);
                padding: 20px;
                border-radius: 8px;
                width: 400px;
                margin: 50px auto;
                text-align: center;
                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            }

            .form-container input,.form-container button {
                width: 100%;
                margin-bottom: 10px;
            }
            .form-container textarea {
                width: 100%;
                margin-bottom: 10px;
            }
            .form-container h2 {
                margin-bottom: 15px;
            }
        </style>
        <title>Fill your lot form</title>
    </head>
    <body>
        <?php
    
            $item_id = $_POST['item_id'];

            $host = "localhost";
            $dbusername = "root";
            $dbpassword = "3215979361";
            $dbname = "auth";
    
            $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);
    
            if($conn->connect_error){
                die("Connetion failed: ". $conn->connect_error);
            }
            
            $name = $_SESSION["username"];
            $infoquery = "SELECT balance FROM user_info WHERE user_info.username='$name'";
            $result = $conn->query($infoquery);
            $info = mysqli_fetch_all($result, MYSQLI_ASSOC);

            $balance = $info[0]['balance'];

        ?>
        <div class="top-bar">
            <div class="top-left">
                Welcome, <?php echo $name; ?>
                <br />
                Balance: $<?php echo $balance ?>
                <a href="home.php" class="btn btn-light">Home</a>
                <a href="user_info.php?type=buy" class="btn btn-light">User Info</a>
                <a href="new_lot.php" class="btn btn-light">Post Your Item</a>
            </div>
            <a href="login_page.php" class="btn btn-light logout-btn">Log Out</a>
        </div>
        <div class="form-container">
            <form action="leave_request.php" method="POST">
            
                <textarea name="user_review" rows="4" placeholder="Leave your review here..." required></textarea>
                
                <?php echo "<input type='hidden' name='item_id' value='" . $item_id . "'>"; ?>

                <input type="submit" name="refund_btn" value="Refund" class="btn btn-success">
                <input type="submit" name="report_btn" value="Report and refund" class="btn btn-success">

            </form>
        </div>
    </body>
</html>