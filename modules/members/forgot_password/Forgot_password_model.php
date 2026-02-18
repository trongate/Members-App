<?php
class Forgot_password_model extends Model {

    public function get_member($username) {
        // Returns either an object or false.
        $params = [
        	'username' => $username,
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
        } else {
        	return $rows[0]; // A PHP object.
        }
    }

    public function is_token_valid($token) {
        $record_obj = $this->db->get_one_where('token', $token, 'password_reset_tokens');
        if ($record_obj === false) {
            return false;
        } else {
            return true;
        }
    }

}