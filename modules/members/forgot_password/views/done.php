<!DOCTYPE html>
<html lang="en">
<head>
	<base href="<?= BASE_URL ?>">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="css/trongate.css">
	<link rel="stylesheet" href="members-login_module/css/login.css">
	<title>Password Reset</title>
</head>
<body>
	<div class="container-xs">
		<h1>Thanks</h1>
		<p>Thank you for submitting a password reset request.</p>
		<p>If there an account matches the details that you 
		submitted, then an email has been sent to the account holder's email address.</p>
		<p>Please check your email inbox and follow the instructions provided.  If the email 
		appears to have not arrived, wait a few minutes and don't forget to check your inbox.</p>
		<p class="text-center"><?= anchor(BASE_URL, 'Return to homepage') ?></p>
	</div>
</body>
</html>