<?php
class Login_model extends Model {

    /**
     * Validate submitted login credentials.
     *
     * Confirms that a matching confirmed member exists
     * and that the submitted password matches the stored hash.
     *
     * @param string $submitted_username Username or email address.
     * @param string $submitted_password Plain text password.
     *
     * @return bool|string Returns true if credentials are valid,
     *                     otherwise an error message string.
     */
    public function login_check(string $submitted_username, string $submitted_password): bool|string {
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

    /**
     * Attempt to locate a confirmed member by username or email address.
     *
     * @param string $submitted_username Username or email address.
     *
     * @return object|false Returns the member object if found,
     *                      otherwise false.
     */
    public function attempt_find_matching_user(string $submitted_username): object|false {
        $params = [
            'username' => $submitted_username,
            'email_address' => $submitted_username
        ];

        $sql = 'SELECT * FROM members 
                     WHERE (username = :username OR email_address = :email_address) 
                     AND confirmed = 1';
        $rows = $this->db->query_bind($sql, $params, 'object');

        if (empty($rows)) {
            return false;
        }

        $member_obj = $rows[0];
        return $member_obj;
    }

}