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
		<h1>Ouch!</h1>
		<p class="text-center">Your reset token has expired, or it was never valid in the first place.</p>
		<p class="text-center"><?= anchor(BASE_URL, 'Return to homepage') ?></p>
	</div>
</body>
</html>