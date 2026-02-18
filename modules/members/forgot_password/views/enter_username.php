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
		<h1>Password Reset</h1>
		<p>To start the password reset process, please submit your username or 
		email address using the form below.</p>
        <?php
        echo validation_errors();
        echo form_open('members-forgot_password/submit_username');
        echo form_label('Username or Email Address');
        $input_attr = [
            'placeholder' => 'Enter username or email address here...',
            'autocomplete' => 'off'
        ];
        echo form_input('username', $username, $input_attr);
        echo '<div class="text-center mt-1">';
        echo anchor('members-login', 'Cancel', array('class' => 'button alt'));
        echo form_submit('submit', 'Submit');
        echo '</div>';
        echo form_close();
        ?>
	</div>
</body>
</html>