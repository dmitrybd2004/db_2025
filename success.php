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

            $host = "localhost";
            $dbusername = "root";
            $dbpassword = "3215979361";
            $dbname = "auth";
    
            $conn = new mysqli($host,$dbusername,$dbpassword,$dbname);
    
            if($conn->connect_error){
                die("Connetion failed: ". $conn->connect_error);
    
            }

            if (isset($_GET['user'])) {
                echo "Welcome: ". htmlspecialchars($_GET['user']) . "</p>";
                $name = htmlspecialchars($_GET['user']); // Retrieves everything after "?"
            }
        ?>

        <a href="user_info.php?user=<?php echo htmlspecialchars($name); ?>">
            <button>User info</button>
        </a>
        <br />
        <a href="new_lot.php?user=<?php echo htmlspecialchars($name); ?>">
            <button>Post your item</button>
        </a>

        <div id="display-image">
            <?php
                $query = "SELECT image_name FROM lot";
                $result = $conn->query($query);

                while ($data = mysqli_fetch_assoc($result)) {
            ?>
                <img src="./image/<?php echo $data['image_name']; ?>">
            <?php
                }
            ?>
        </div>

    </body>
</html>