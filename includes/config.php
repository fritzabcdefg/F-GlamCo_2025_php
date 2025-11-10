<?php 
$db_host = "localhost:3306";
$db_username = "root";
$db_passwd = "";

$conn = mysqli_connect($db_host, $db_username, $db_passwd) or die("Could not connect!\n");

// echo "Connection established.\n";
$db_name = "db_makeup_shop_2025";
mysqli_select_db($conn, $db_name) or die("Could not select the database $dbname!\n". mysqli_error($conn));
/*
 Mail configuration: two compatible modes supported
 1) Legacy socket helper (smtp_send_mail) which reads MAIL_* globals
 2) PHPMailer (recommended) when installed via Composer

 Update the MAIL_* values below to match your Mailtrap or SMTP provider.
*/

// Try to load Composer autoload (for PHPMailer). If available, require it.
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
	require_once $vendorAutoload;
}

// Default MAIL_* fallbacks (used by the legacy smtp helper if PHPMailer isn't available)
$MAIL_HOST = isset($MAIL_HOST) ? $MAIL_HOST : 'sandbox.smtp.mailtrap.io';
$MAIL_PORT = isset($MAIL_PORT) ? $MAIL_PORT : 2525;
$MAIL_USER = isset($MAIL_USER) ? $MAIL_USER : 'ad3f31aa582735';
$MAIL_PASS = isset($MAIL_PASS) ? $MAIL_PASS : 'aff40fd726f446';
$MAIL_FROM = isset($MAIL_FROM) ? $MAIL_FROM : 'no-reply@example.com';
$MAIL_FROM_NAME = isset($MAIL_FROM_NAME) ? $MAIL_FROM_NAME : 'F&L Glam Co';

// If PHPMailer is available, pre-configure a reusable instance
if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
	try {
		$phpmailer = new \PHPMailer\PHPMailer\PHPMailer(true);
		$phpmailer->isSMTP();
		$phpmailer->Host = $MAIL_HOST;
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = $MAIL_USER;
		$phpmailer->Password = $MAIL_PASS;
		$phpmailer->Port = $MAIL_PORT;
		// Optional encryption - uncomment if your provider requires it
	// $phpmailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
		$phpmailer->setFrom($MAIL_FROM, $MAIL_FROM_NAME);
	} catch (Exception $e) {
		// If configuration fails, ensure $phpmailer is not set so callers fall back
		if (isset($phpmailer)) unset($phpmailer);
	}
}

?>