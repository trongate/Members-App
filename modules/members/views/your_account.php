<div class="container-xs text-left">
    <h2 class="text-center">Your Account</h2>
    <?= flashdata() ?>
    <div class="welcome-message text-center">
        <?= $info ?>
    </div>

    <p class="text-right sm">
        <?php 
        echo anchor('members/update_account', 'Update Account Details', array('class' => 'button alt mt-0')); 
        $btn_text = ($set_password_required === true) ? 'Set Your Password' : 'Change Password';
        echo anchor('members/update_password', $btn_text, array('class' => 'button alt mt-0')); 
        echo anchor($logout_url, 'Logout', array('class' => 'button mt-0')); 
        ?>
    </p>

    <table>
        <thead>
            <tr>
                <th colspan="2" class="text-center">Account Information</th>
            </tr>
        </thead>
        <tbody class="sm">
            <tr>
                <td>Username</td>
                <td><?= out($member->username) ?></td>
            </tr>
            <tr>
                <td>First Name</td>
                <td><?= out($first_name) ?></td>
            </tr>
            <tr>
                <td>Last Name</td>
                <td><?= out($last_name) ?></td>
            </tr>
            <tr>
                <td>Email Address</td>
                <td><?= out($member->email_address) ?></td>
            </tr>
            <tr>
                <td>User Level</td>
                <td><?= out($member->user_level) ?></td>
            </tr>
        </tbody>
    </table>

    <table class="mt-1">
        <thead>
            <tr>
                <th colspan="2" class="text-center">Account Activity</th>
            </tr>
        </thead>
        <tbody class="sm">
            <tr>
                <td>Join Date</td>
                <td><?= date('l jS F Y', $member->date_created) ?></td>
            </tr>
            <tr>
                <td>Days As Members</td>
                <td><?= $days_as_member ?></td>
            </tr>
            <tr>
                <td>Total Logins</td>
                <td><?= $member->num_logins ?></td>
            </tr>
        </tbody>
    </table>
</div>

<style>
.welcome-message {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 1.5em;
    margin-bottom: 1em;
    border-left: 4px solid var(--primary);
}

td {
    width: 50%;
}
</style>
