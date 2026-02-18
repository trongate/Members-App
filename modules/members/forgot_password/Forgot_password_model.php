<?php
class Forgot_password_model extends Model {

    /**
     * Retrieve a member by username OR email address.
     *
     * Returns a member object if found, otherwise false.
     *
     * @param string $username
     * @return object|false
     */
    public function get_member(string $username): object|false {

        $params = [
            'username'      => $username,
            'email_address' => $username
        ];

        $sql = 'SELECT * FROM members 
                WHERE 
                username = :username 
                OR 
                email_address = :email_address';

        $rows = $this->db->query_bind($sql, $params, 'object');

        if (empty($rows)) {
            return false;
        }

        return $rows[0];
    }

    /**
     * Determine whether a password reset token exists.
     *
     * @param string $token
     * @return bool
     */
    public function is_token_valid(string $token): bool {

        $record_obj = $this->db->get_one_where('token', $token, 'password_reset_tokens');

        return ($record_obj !== false);
    }
}