<!DOCTYPE html>
<html lang="en">
<head>
    <base href="<?= BASE_URL ?>">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="css/trongate.css">
	<link rel="stylesheet" href="members-login_module/css/login.css">
	<title>Login</title>
</head>
<body>
    <div class="container-xs">
        <h1>Login</h1>
        <div class="text-center mt-3">

            <?php
            echo validation_errors();
            echo form_open('members-login/submit_login');
            echo form_label('Username or Email Address');
            $username_attr = [
                'placeholder' => 'Enter username or email address...',
                'autocomplete' => 'off'
            ];
            echo form_input('username', '', $username_attr);

            echo form_label('Password');
            echo form_password('password', '', array('placeholder' => 'Enter password...'));

            echo '<div>';
            echo anchor(BASE_URL, 'Cancel', array('class' => 'button alt'));
            echo form_submit('submit', 'Submit');
            echo '</div>';
            echo form_close();
            ?>

        </div>
        <p class="text-center">Not a member?  <?= anchor('join', 'Join now!') ?></p>
    </div>
</body>
</html>