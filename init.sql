CREATE DATABASE IF NOT EXISTS auth;
USE auth;

CREATE TABLE IF NOT EXISTS login (
    username CHAR(16) PRIMARY KEY,
    password CHAR(16) NOT NULL UNIQUE,
    banned BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS user_info (
    username CHAR(16) PRIMARY KEY,
    balance DECIMAL(10, 2) NOT NULL DEFAULT 0,
    rating INT NOT NULL DEFAULT 0,
    products_bought INT NOT NULL DEFAULT 0,
    products_sold INT NOT NULL DEFAULT 0,
    FOREIGN KEY (username) REFERENCES login(username)
);

CREATE TABLE IF NOT EXISTS roles (
    username CHAR(16) PRIMARY KEY,
    role CHAR(16) NOT NULL DEFAULT 'user',
    FOREIGN KEY (username) REFERENCES user_info(username)
);

CREATE TABLE IF NOT EXISTS lot (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_name CHAR(16) NOT NULL,
    buyer_name CHAR(16) DEFAULT NULL,
    item_name CHAR(16) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_name VARCHAR(256) NOT NULL UNIQUE,
    review TINYINT NOT NULL DEFAULT 0,
    FOREIGN KEY (seller_name) REFERENCES user_info(username),
    FOREIGN KEY (buyer_name) REFERENCES user_info(username),
    CHECK (price > 0)
);

CREATE TABLE IF NOT EXISTS refunds (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    item_name CHAR(16) NOT NULL,
    description VARCHAR(256) NOT NULL,
    processed TINYINT NOT NULL DEFAULT 0,
    report_type TINYINT NOT NULL,
    accepted TINYINT NOT NULL DEFAULT 0,
    sent_by CHAR(16) NOT NULL,
    FOREIGN KEY (item_id) REFERENCES lot(item_id),
    FOREIGN KEY (sent_by) REFERENCES user_info(username)
);