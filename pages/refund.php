<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: rgb(244, 255, 183);
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
        .form-container input,
        .form-container textarea,
        .form-container button {
            width: 100%;
            margin-bottom: 10px;
        }
        .form-container h2 {
            margin-bottom: 15px;
        }
    </style>
    <title>Refund Request</title>
</head>
<body>
    <div class="top-bar">
        <div class="top-left" id="user-info">
        </div>
        <button onclick="logout()" class="btn btn-light logout-btn">Log Out</button>
    </div>
    <div class="form-container">
        <form id="refundForm">
            <textarea name="user_review" rows="4" placeholder="Leave your review here..." required></textarea>
            <input type="hidden" name="item_id" value="<?php echo $_GET['item_id']; ?>">
            <div id="successMessage" style="display: none; color: green; margin-top: 10px;">
                Refund request submitted successfully!
            </div>
            <br>
            <button type="button" onclick="submitRefund(0)" class="btn btn-success">Refund</button>
            <button type="button" onclick="submitRefund(1)" class="btn btn-success">Report and Refund</button>
        </form>
    </div>
    <script>
        async function fetchUserInfo() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = "/login/pages/login_page.php";
                return;
            }

            try {
                const response = await fetch('/login/api/users/info/', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    document.getElementById('user-info').innerHTML = `
                        Welcome, ${data.username}
                        <br />
                        Balance: $${data.balance}
                        <a href="../pages/home.php" class="btn btn-light">Home</a>
                        <a href="../pages/user_info.php?type=buy" class="btn btn-light">User Info</a>
                        <a href="../pages/new_lot.php" class="btn btn-light">Post Your Item</a>
                    `;
                } else {
                    throw new Error('Failed to fetch user info');
                }
            } catch (error) {
                alert('Failed to fetch user info. Please log in again.');
                window.location.href = "/login/pages/login_page.php";
            }
        }

        async function submitRefund(reportType) {
            const form = document.getElementById('refundForm');
            const formData = new FormData(form);
            const data = {
                item_id: formData.get('item_id'),
                user_review: formData.get('user_review'),
                report_type: reportType
            };

            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = "/login/pages/login_page.php";
                return;
            }

            try {
                const response = await fetch('/login/api/refunds/create/', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(data)
                });

                if (response.ok) {
                    const successMessage = document.getElementById('successMessage');
                    successMessage.style.display = 'block';

                    form.reset();
                } else {
                    const result = await response.json();
                    alert(result.error || 'Failed to submit refund request');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            }
        }

        function logout() {
            localStorage.removeItem('token');
            window.location.href = "/login/pages/login_page.php";
        }

        fetchUserInfo();
    </script>
</body>
</html>