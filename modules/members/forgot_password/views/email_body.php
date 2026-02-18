<p>Dear <?= out($member_name) ?>,</p>

<p>We have received a password reset request for the <?= WEBSITE_NAME ?> website.</p>

<p>You can reset your password by going to the following URL:</p>

<p><?= anchor($reset_url) ?></p>

<p>If you have received this email in error, you can safely ignore it.</p>

<p>Regards,</p>

<p><?= OUR_NAME ?></p>