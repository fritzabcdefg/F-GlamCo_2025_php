<?php
// SMTP sender with STARTTLS support so it works with Gmail (smtp.gmail.com:587) and Mailtrap.
// For production prefer PHPMailer or similar. This is a minimal helper for local/testing.

function smtp_send_mail($to, $subject, $html_body, $from = null, $from_name = null) {
	global $MAIL_HOST, $MAIL_PORT, $MAIL_USER, $MAIL_PASS, $MAIL_FROM, $MAIL_FROM_NAME, $phpmailer;

	// If a PHPMailer instance was configured in includes/config.php, prefer it.
	if (isset($phpmailer) && class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
		try {
			// clone the configured instance so we don't mutate the shared one
			$mail = clone $phpmailer;
			// ensure recipients and body are set for this message
			$mail->clearAllRecipients();
			$mail->addAddress($to);
			if ($from) {
				$mail->setFrom($from, $from_name ?? $MAIL_FROM_NAME);
			}
			$mail->Subject = $subject;
			$mail->isHTML(true);
			$mail->Body = $html_body;
			$mail->send();
			return ['success' => true];
		} catch (\Exception $e) {
			return ['success' => false, 'error' => $mail->ErrorInfo ?? $e->getMessage()];
		}
	}

	$from = $from ?? $MAIL_FROM;
	$from_name = $from_name ?? $MAIL_FROM_NAME;

	$host = $MAIL_HOST;
	$port = (int) $MAIL_PORT;
	$user = $MAIL_USER;
	$pass = $MAIL_PASS;

	$errno = 0; $errstr = '';
	$fp = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
	if (!$fp) {
		return ['success' => false, 'error' => "Socket error: $errstr ($errno)"];
	}

	// helper to read server response lines until a line with 'xxx ' (space) is reached
	$read_response = function() use ($fp) {
		$out = '';
		while (($line = fgets($fp, 515)) !== false) {
			$out .= $line;
			// line starts with 3 digits and a space indicates end of multi-line response
			if (preg_match('/^[0-9]{3} /', $line)) break;
		}
		return $out;
	};

	// initial greeting
	$resp = $read_response();

	// EHLO
	fputs($fp, "EHLO localhost\r\n");
	$resp = $read_response();

	// If server supports STARTTLS (Gmail) and we're on a non-encrypted port (usually 587), enable it
	if (stripos($resp, 'STARTTLS') !== false || $port === 587) {
		fputs($fp, "STARTTLS\r\n");
		$resp = $read_response();
		// enable crypto
		if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
			fclose($fp);
			return ['success' => false, 'error' => 'Failed to enable TLS/STARTTLS'];
		}
		// EHLO again after STARTTLS
		fputs($fp, "EHLO localhost\r\n");
		$resp = $read_response();
	}

	// AUTH LOGIN
	fputs($fp, "AUTH LOGIN\r\n");
	$resp = $read_response();
	fputs($fp, base64_encode($user) . "\r\n");
	$resp = $read_response();
	fputs($fp, base64_encode($pass) . "\r\n");
	$resp = $read_response();

	// MAIL FROM
	fputs($fp, "MAIL FROM:<{$from}>\r\n");
	$resp = $read_response();

	// RCPT TO
	fputs($fp, "RCPT TO:<{$to}>\r\n");
	$resp = $read_response();

	// DATA
	fputs($fp, "DATA\r\n");
	$resp = $read_response();

	// Headers
	$headers = [];
	$headers[] = "From: {$from_name} <{$from}>";
	$headers[] = "To: {$to}";
	$headers[] = "Subject: {$subject}";
	$headers[] = "MIME-Version: 1.0";
	$headers[] = "Content-Type: text/html; charset=\"UTF-8\"";
	$headers[] = "Content-Transfer-Encoding: 8bit";

	$message = implode("\r\n", $headers) . "\r\n\r\n" . $html_body . "\r\n.\r\n";
	fputs($fp, $message);
	$resp = $read_response();

	// QUIT
	fputs($fp, "QUIT\r\n");
	$resp = $read_response();

	fclose($fp);

	// Accept 250/221 as success indicators
	if (stripos($resp, '250') !== false || stripos($resp, '221') !== false) {
		return ['success' => true];
	}

	return ['success' => false, 'error' => $resp];
}

?>
