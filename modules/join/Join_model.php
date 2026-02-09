<?php
/**
 * Join Model
 *
 * Handles data operations for user registration and membership creation.
 */
class Join_model extends Model {

    /**
     * Get registration data from POST request
     *
     * Extracts and sanitizes form data from POST request for registration.
     *
     * @return array<string, string> Array containing sanitized form data
     */
    public function get_data_from_post(): array {
        $data = [
            'username' => post('username', true),
            'first_name' => post('first_name', true),
            'last_name' => post('last_name', true),
            'email_address' => post('email_address', true)
        ];

        return $data;
    }

    /**
     * Check if username is available
     *
     * Verifies if a username is already taken in the members table.
     *
     * @param string $username The username to check
     * @return bool|string Returns true if available, error message if taken
     */
    public function username_check(string $username): bool|string {

        // Only allow letters (a-z, A-Z) and numbers (0-9).
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            return 'The username can only contain letters and numbers';
        }

        $user_obj = $this->db->get_one_where('username', $username, 'members');
        if ($user_obj === false) {
            // The username is available!
            return true;
        } else {
            $error_msg = 'The username that you submitted is not available.';
            return $error_msg;
        }
    }

    /**
     * Check if email address is available
     *
     * Verifies if an email address is already taken in the members table.
     *
     * @param string $email_address The email address to check
     * @return bool|string Returns true if available, error message if taken
     */
    public function email_check(string $email_address): bool|string {
        $user_obj = $this->db->get_one_where('email_address', $email_address, 'members');
        if ($user_obj === false) {
            // The email address is available!
            return true;
        } else {
            $error_msg = 'The email address that you submitted is not available.';
            return $error_msg;
        }
    }

    /**
     * Create a new member record
     *
     * Creates records in both trongate_users and members tables for a new member.
     *
     * @param array<string, string> $data Member data from registration form
     * @return int The ID of the newly created member record
     */
    public function create_new_member_record(array $data): int {
        // Create a record on the trongate_users table.
        $trongate_user_data = [
            'code' => make_rand_str(32),
            'user_level_id' => 2
        ];

        $data['trongate_user_id'] = $this->db->insert($trongate_user_data, 'trongate_users');

        // Create a record on the members table.
        $this->module('encryption');
        $data['first_name'] = $this->encryption->encrypt($data['first_name']);
        $data['last_name'] = $this->encryption->encrypt($data['last_name']);
        $data['date_created'] = time();
        $data['num_logins'] = 0;
        $data['password'] = '';
        $data['user_token'] = make_rand_str(32);
        $data['confirmed'] = 0;
        $member_id = $this->db->insert($data, 'members');

        return $member_id;
    }

    public function attempt_activate_account($user_token) {
        $member_obj = $this->db->get_one_where('user_token', $user_token, 'members');

        if ($member_obj === false) {
            return false;
        }

        $data = [
            'user_token' => '',
            'confirmed' => 1
        ];

        $update_id = (int) $member_obj->id;
        $this->db->update($update_id, $data, 'members');
        return $member_obj;
    }

    public function get_member_obj($member_id) {
        $member_obj = $this->db->get_where($member_id, 'members');
        return $member_obj; // returns either an object or false (bool)
    }

}