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
                echo "Welcome: ". htmlspecialchars($_GET['user']) . "</p>";
                $name = htmlspecialchars($_GET['user']); // Retrieves everything after "?"
            }
        ?>

        <a href="user_info.php?user=<?php echo htmlspecialchars($name); ?>">
            <button>User info</button>
        </a>
    </body>
</html>