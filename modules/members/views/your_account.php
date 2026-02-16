<div class="container-xs mt-3">
    <h2>Your Account</h2>

    <table>
        <tr>
            <th>Username</th>
            <td><?= out($member->username) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= out($member->email_address) ?></td>
        </tr>
    </table>

    <p>
        <?= anchor('members/update_password', 'Change Password', array('class' => 'button alt')) ?>
        <?= anchor($logout_url, 'Logout', array('class' => 'button')) ?>
    </p>

    <p class="mt-3"><?= anchor('forums', 'Return To Discussion Forums') ?></p>

</div>

<p class="xs">Apologies for this page being very rubbish.  We'll have a much better page up shortly!  If you need to change any of your details, please get in touch.</p>

