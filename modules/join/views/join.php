<h1 class="mt-1">Join <?= OUR_NAME ?></h1>
<p>To join <?= OUR_NAME ?>, please fill out the form below and
then hit 'Submit'.</p>

<div class="container-xs">
    <?php
    echo validation_errors();
    echo form_open('join/submit');
    echo form_label('Username');
    $username_attr = [
        'placeholder' => 'Enter username...',
        'autocomplete' => 'off'
    ];
    echo form_input('username', $username, $username_attr);

    echo form_label('First Name');
    $first_name_attr = [
        'placeholder' => 'Enter first name...',
        'autocomplete' => 'off'
    ];
    echo form_input('first_name', $first_name, $first_name_attr);

    echo form_label('Last Name');
    $last_name_attr = [
        'placeholder' => 'Enter first name...',
        'autocomplete' => 'off'
    ];
    echo form_input('last_name', $last_name, $last_name_attr);

    echo form_label('Email Address');
    $email_attr = [
        'placeholder' => 'Enter email address...',
        'autocomplete' => 'off'
    ];
    echo form_email('email_address', $email_address, $email_attr);

    echo '<div class="text-center">';
    echo anchor(BASE_URL, 'Cancel', array('class' => 'button alt'));
    echo form_submit('submit', 'Submit');
    echo '</div>';

    echo form_close();
    ?>
</div>