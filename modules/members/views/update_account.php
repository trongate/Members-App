<h1 class="mt-1">Update Account Details</h1>
<p>Please update your account information below and then hit 'Update Account'.</p>

<div class="container-xs">
    <?php
    echo validation_errors();
    echo form_open($form_location);

    echo form_label('Username');
    $username_attr = [
        'placeholder' => 'Enter username...',
        'autocomplete' => 'off',
        'required' => true
    ];
    echo form_input('username', $username, $username_attr);

    echo form_label('First Name');
    $first_name_attr = [
        'placeholder' => 'Enter first name...',
        'autocomplete' => 'off',
        'required' => true
    ];
    echo form_input('first_name', $first_name, $first_name_attr);

    echo form_label('Last Name');
    $last_name_attr = [
        'placeholder' => 'Enter last name...',
        'autocomplete' => 'off',
        'required' => true
    ];
    echo form_input('last_name', $last_name, $last_name_attr);

    echo form_label('Email Address');
    $email_address_attr = [
        'placeholder' => 'Enter email address...',
        'autocomplete' => 'off',
        'required' => true
    ];
    echo form_email('email_address', $email_address, $email_address_attr);

    echo '<div class="text-center">';
    echo anchor('members/your_account', 'Cancel', array('class' => 'button alt'));
    echo form_submit('submit', 'Update Account');
    echo '</div>';

    echo form_close();
    ?>
</div>