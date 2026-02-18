<!DOCTYPE html>
<html lang="en">
<head>
	<base href="<?= BASE_URL ?>">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="css/trongate.css">
	<link rel="stylesheet" href="members-login_module/css/login.css">
	<title>Activate Account</title>
</head>
<body>
	<div class="container-xs">
		<h1>Activate Account</h1>
		<p class="text-center">To activate your account, click the 'Activate Account' button below.</p>
        <?php
        echo form_open('join/submit_activate_account');
        echo form_hidden('user_token', $user_token);
        echo '<div class="text-center">';
        echo form_submit('submit', 'Activate Account');
        echo '</div>';
        echo form_close();
        ?>
	</div>
</body>
</html>