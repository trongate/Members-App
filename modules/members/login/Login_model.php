<?php
class Login_model extends Model {

    public function login_check($submitted_username, $submitted_password) {
        $error_msg = 'You did not submit a correct username/email and/or password.';
        $member_obj = $this->attempt_find_matching_user($submitted_username);

        if ($member_obj === false) {
            return $error_msg;
        }

        $stored_password = $member_obj->password;
        $password_valid = password_verify($submitted_password, $stored_password);
        if ($password_valid === false) {
            return $error_msg;
        }

        return true;
    }

    public function attempt_find_matching_user($submitted_username) {
        $params = [
            'username' => $submitted_username,
            'email_address' => $submitted_username
        ];

        $sql = 'SELECT * FROM members 
                     WHERE (username = :username OR email_address = :email_address) 
                     AND confirmed = 1';
        $rows = $this->db->query_bind($sql, $params, 'object');

        if (empty($rows)) {
            return false; // No matching user found.
        }

        $member_obj = $rows[0];
        return $member_obj;
    }

}