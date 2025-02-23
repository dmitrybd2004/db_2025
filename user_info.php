<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome</title>
    </head>
    <body>
        <?php
            if (isset($_GET['user'])) {
        
                //Database connection
        
                $host = "localhost";
                $dbusername = "root";
                $dbpassword = "3215979361";
                $dbname = "auth";
        
                $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);
        
                if($conn->connect_error){
                    die("Connetion failed: ". $conn->connect_error);
        
                }

                echo "Welcome: ". htmlspecialchars($_GET['user']) . "</p>";

                $name = htmlspecialchars($_GET['user']);
                $infoquery = "SELECT * FROM user_info WHERE user_info.username='$name'";
                $result = $conn->query($infoquery);
                $info = mysqli_fetch_all($result, MYSQLI_ASSOC);

            }
        ?>
        <a href="success.php?user=<?php echo htmlspecialchars($name); ?>">
            <button>Home</button>
        </a>
        <br>
        <?php
        if (isset($_GET['user'])) {
            echo "Your balance is: " . $info[0]['balance'];
        }
        ?>
        <br>
        <?php
        if (isset($_GET['user'])) {
            echo "Your rating is: " . $info[0]['rating'];
        }
        ?>
        <br>
        <?php
        if (isset($_GET['user'])) {
            echo "You bought " . $info[0]['products_bought'] . " items";
        }
        ?>
        <br>
        <?php
        if (isset($_GET['user'])) {
            echo "You sold " . $info[0]['products_sold'] . " items";
        }
        ?>
        <br>
        <form action="deposit.php?user=<?php echo htmlspecialchars($name); ?>" method="POST">
            <input 
                type="number" 
                name="amount" 
                class="form-control" 
                placeholder="Enter deposit"
            /><br />
                <input type="submit" name="deposit_btn" value="Add funds" class="btn btn-success">
        </form>
        <?php
            if (isset($_GET['error'])) {
                echo "<p style='color:red;'>" ."Error: ". htmlspecialchars($_GET['error']) . "</p>";
            }
        ?>
    </body>
</html>