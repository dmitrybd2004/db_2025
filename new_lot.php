<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Fill your lot form</title>
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

                $name = htmlspecialchars($_GET['user']);

            }
        ?>
        <a href="success.php?user=<?php echo htmlspecialchars($name); ?>">
            <button>Home</button>
        </a>
        <form action="create_lot.php?user=<?php echo htmlspecialchars($name); ?>" method="POST" enctype="multipart/form-data">
            <input 
                type="text" 
                name="item_name" 
                class="form-control" 
                placeholder="Enter the item name"
            /><br />
            <input 
                type="number" 
                name="price" 
                class="form-control" 
                placeholder="Enter the price"
            /><br />
            <input 
                type="file" 
                name="item_image"
                accept="image/png, image/jpeg, image/jpg" 
            /><br />
            <input type="submit" name="create_form_btn" value="Create lot" class="btn btn-success">
        </form>
        <?php
            if (isset($_GET['error'])) {
                echo "<p style='color:red;'>" ."Error: ". htmlspecialchars($_GET['error']) . "</p>";
            }
        ?>
    </body>
</html>