<!DOCTYPE html>
<html lang="en">
<head>
	<base href="<?= BASE_URL ?>">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="css/trongate.css">
	<link rel="stylesheet" href="members-login_module/css/login.css">
	<title>Token Invalid</title>
</head>
<body>
	<div class="container-xs">
		<h1>Password Reset</h1>
		<p class="text-center">To reset your password, click the 'Reset Password' button below.</p>
        <?php
        echo form_open('members-forgot_password/submit_confirm_reset');
        echo form_hidden('token', $token);
        echo '<div class="text-center">';
        echo form_submit('submit', 'Reset Password');
        echo '</div>';
        echo form_close();
        ?>
	</div>
</body>
</html>