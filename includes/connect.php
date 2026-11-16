<?php
$host = "localhost";
$username = "root";  // default XAMPP username
$password = "";      // default XAMPP password is empty
$database = "teddy_bear_shop";

$con = mysqli_connect($host, $username, $password, $database);

if(!$con) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
?>
