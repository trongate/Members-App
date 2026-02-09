<p>Dear <?= out($first_name).' '.out($last_name) ?>,</p>

<p>Thank you for joining <?= OUR_NAME ?>.</p>

<p>To activate your account, please go to the following URL:</p>

<p><?= anchor($activation_url) ?></p>

<p>If you have received this email in error, please ignore it and accept our apologies.</p>

<p>Regards,</p>

<p>Trongate Support</p>