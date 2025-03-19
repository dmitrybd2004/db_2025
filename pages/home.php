<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <title>Welcome</title>
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
            .container {
                margin-top: 50px;
                text-align: center;
            }
            .search-form {
                margin: 10px 0;
            }
            .items-container {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                justify-content: center;
            }
            .item {
                border: 2px solid black;
                padding: 10px;
                margin: 10px;
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 250px;
                text-align: center;
                background-color: #fff;
                border-radius: 5px;
            }
            .item img {
                width: 200px;
                height: 250px;
            }
            .search-frame {
                border: 2px solid #ddd;
                padding: 10px;
                margin: 10px auto;
                width: 30%;
                background: #f8f9fa;
                border-radius: 10px;
                text-align: center;
            }
            .search-frame .form-group {
                display: flex;
                justify-content: space-between;
            }
            .search-frame input {
                width: 100%;
                margin-bottom: 3px;
                padding: 4px;
                font-size: 14px;
            }
            .search-frame .form-group input {
                width: 48%;
            }
            .search-frame button {
                padding: 5px 10px;
                font-size: 14px;
            }
            .sort-search-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 10px;
                padding-top: 5px;
            }
            .sort-by {
                text-align: left;
            }
            hr {
                border: 1;
                clear: both;
                display: block;
                width: 110%;
                background-color: black;
                height: 3px;
            }
            .welcome-balance-container {
                display: flex;
                align-items: center;
                gap: 20px;
            }

            .buttons-container {
                display: flex;
                gap: 10px;
                margin-left: auto;
            }
        </style>
        <script>

            function saveScrollPosition() {
                sessionStorage.setItem("scrollPosition", window.scrollY);
            }

            function restoreScrollPosition() {
                const scrollPosition = sessionStorage.getItem("scrollPosition");
                if (scrollPosition) {
                    setTimeout(() => {
                        window.scrollTo({
                            top: parseInt(scrollPosition),
                            behavior: "instant"
                        });
                        sessionStorage.removeItem("scrollPosition");
                    }, 100);
                }
            }

            document.addEventListener("DOMContentLoaded", async function () {
                try {
                    await fetchUserData();
                    await fetchItems();
                } catch (error) {
                    alert("Failed to initialize page. Please try again.");
                }
            });

            async function fetchUserData() {
                const token = localStorage.getItem('token');
                if (!token) {
                    window.location.href = "/login/pages/login_page.php";
                    return;
                }

                try {
                    const response = await fetch('/login/api/users/info/', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`
                        }
                    });

                    if (!response.ok) {
                        throw new Error("Failed to fetch user data");
                    }

                    const data = await response.json();
                    document.querySelector('.top-left').innerHTML = `
                        <div class="welcome-balance-container">
                            <div>
                                Welcome, ${data.username}
                                <br>
                                Balance: $<span id="user-balance">${data.balance}</span>
                            </div>
                            <div class="buttons-container">
                                <a href="../pages/home.php" class="btn btn-light">Home</a>
                                <a href="../pages/user_info.php?type=buy" class="btn btn-light">User Info</a>
                                <a href="../pages/new_lot.php" class="btn btn-light">Post Your Item</a>
                            </div>
                        </div>
                    `;
                } catch (error) {
                    alert("Failed to fetch user data. Please log in again.");
                    window.location.href = "/login/pages/login_page.php";
                }
            }

            async function fetchItems() {
                const token = localStorage.getItem('token');
                if (!token) {
                    alert("Token not found. Please log in again.");
                    window.location.href = "/login/pages/login_page.php";
                    return;
                }

                const url = new URL('/login/api/lots/available/', window.location.origin);
                const params = new URLSearchParams(window.location.search);

                if (!params.has('sort')) {
                    params.set('sort', 'time');
                }

                params.forEach((value, key) => url.searchParams.append(key, value));

                try {
                    const response = await fetch(url, {
                        headers: {
                            'Authorization': `Bearer ${token}` // Add the Authorization header
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    if (data.status === 'success' && Array.isArray(data.data)) {
                        renderItems(data.data);
                    } else {
                        document.querySelector('.items-container').innerHTML = `<p>Error: Invalid response format</p>`;
                    }
                } catch (error) {
                    document.querySelector('.items-container').innerHTML = `<p>Error loading items. Please try again later.</p>`;
                }
            }

            function renderItems(items) {
                const itemsContainer = document.querySelector('.items-container');
                itemsContainer.innerHTML = '';

                if (items.length === 0) {
                    itemsContainer.innerHTML = `<p>No items available.</p>`;
                    return;
                }

                items.forEach(item => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'item';

                    const price = parseFloat(item.price);

                    itemDiv.innerHTML = `
                        <img src="../image/${item.image_name}" alt="Item Image" width="200" height="250">
                        <hr>
                        <h4>${item.item_name}</h4>
                        <h4>$${price.toFixed(2)}</h4>
                        <h4> Sold by: ${item.seller_name} (${item.rating})</h5>
                        <form onsubmit="buyItem(event, ${item.item_id})">
                            <input type="hidden" name="buyer_name" value="${localStorage.getItem('username')}">
                            <button type="submit" class="btn btn-success" ${price > parseFloat(document.getElementById('user-balance').textContent) ? 'disabled' : ''}>Buy</button>
                        </form>
                    `;

                    itemsContainer.appendChild(itemDiv);
                });

                restoreScrollPosition();
            }

            async function buyItem(event, itemId) {
                event.preventDefault();
                const token = localStorage.getItem('token');

                if (!token) {
                    alert("Token not found. Please log in again.");
                    window.location.href = "/login/pages/login_page.php";
                    return;
                }

                saveScrollPosition();

                const payload = JSON.parse(atob(token.split('.')[0]));
                const buyerName = payload.username;

                if (!buyerName) {
                    alert("Username not found in token. Please log in again.");
                    window.location.href = "/login/pages/login_page.php";
                    return;
                }

                try {
                    const response = await fetch(`/login/api/lots/buy/index.php?id=${itemId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`
                        },
                        body: JSON.stringify({ buyer_name: buyerName })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const data = await response.json();
                    if (data.status === 'success') {
                        window.location.reload();
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    alert("An error occurred. Please try again.");
                }
            }

            function logout() {
                localStorage.removeItem('token');
                localStorage.removeItem('username');
                window.location.href = "/login/pages/login_page.php";
            }
        </script>
    </head>
    <body>
        <div class="top-bar">
            <div class="top-left">
            </div>
            <button onclick="logout()" class="btn btn-light logout-btn">Log Out</button>
        </div>

        <div class="container">
            <div class="search-form">
                <div class="search-frame">
                    <form method="GET" action="">
                        <div class="form-group">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="min_price"
                                placeholder="Minimal price"
                                class="form-control"
                            >
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="max_price"
                                placeholder="Maximal price"
                                class="form-control"
                            >
                        </div>
                        <input
                            type="number"
                            name="min_rating"
                            min="0"
                            placeholder="Minimal rating"
                            class="form-control">
                        <input
                            type="text"
                            name="search"
                            placeholder="Search items..."
                            class="form-control">

                        <div class="form-group sort-search-container">
                            <div class="sort-by">
                                <select name="sort" id="sort" class="form-control">
                                    <option value="time" selected>Sort by: Time of publication</option>
                                    <option value="price_asc">Sort by: Price (Ascending)</option>
                                    <option value="price_desc">Sort by: Price (Descending)</option>
                                    <option value="rating">Sort by: Rating</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <h2>Available Items</h2>
            <div class="items-container">
            </div>
        </div>
    </body>
</html>