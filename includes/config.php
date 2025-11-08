<?php 
$db_host = "localhost:3306";
$db_username = "root";
$db_passwd = "";

$conn = mysqli_connect($db_host, $db_username, $db_passwd) or die("Could not connect!\n");

// echo "Connection established.\n";
$db_name = "db_makeup_shop_2025";
mysqli_select_db($conn, $db_name) or die("Could not select the database $dbname!\n". mysqli_error($conn));

/*
 Mail configuration (for Mailtrap or other SMTP). Fill these with your Mailtrap credentials.
 Example values for Mailtrap (replace with your credentials):
	 $MAIL_HOST = 'smtp.mailtrap.io';
	 $MAIL_PORT = 2525;
	 $MAIL_USER = 'your_mailtrap_user';
	 $MAIL_PASS = 'your_mailtrap_pass';
	 $MAIL_FROM = 'no-reply@example.com';
	 $MAIL_FROM_NAME = 'F&L Glam Co';
*/
$MAIL_HOST = isset($MAIL_HOST) ? $MAIL_HOST : 'smtp.gmail.com';
$MAIL_PORT = isset($MAIL_PORT) ? $MAIL_PORT : 587;
$MAIL_USER = isset($MAIL_USER) ? $MAIL_USER : 'fritziecadao@gmail.com';
$MAIL_PASS = isset($MAIL_PASS) ? $MAIL_PASS : 'your_mailtrap_pass';
$MAIL_FROM = isset($MAIL_FROM) ? $MAIL_FROM : 'example.com';
$MAIL_FROM_NAME = isset($MAIL_FROM_NAME) ? $MAIL_FROM_NAME : 'F&L Glam Co';
?>