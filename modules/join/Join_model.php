<?php
class Join_model extends Model {

    public function get_data_from_post() {

        $data = [
            'username' => post('username', true),
            'first_name' => post('first_name', true),
            'last_name' => post('last_name', true),
            'email_address' => post('email_address', true)
        ];

        return $data;
    }

    public function username_check($username) {
        $user_obj = $this->db->get_one_where('username', $username, 'members');
        if ($user_obj === false) {
            // The username is available!
            return true;
        } else {
            $error_msg = 'The username that your submitted is not available.';
            return $error_msg;
        }
    }

    public function email_check($email_address) {
        $user_obj = $this->db->get_one_where('email_address', $email_address, 'members');
        if ($user_obj === false) {
            // The email address is available!
            return true;
        } else {
            $error_msg = 'The email address that your submitted is not available.';
            return $error_msg;
        }
    }

    public function create_new_member_record($data) {

        // Create a record on the trongate_users table.
        $trongate_user_data = [
            'code' => make_rand_str(32),
            'user_level_id' => 2
        ];

        $data['trongate_user_id'] = $this->db->insert($trongate_user_data, 'trongate_users');

        // Create a record on the members table.
        $data['date_created'] = time();
        $data['num_logins'] = 0;
        $data['password'] = '';
        $data['user_token'] = '';
        $member_id = $this->db->insert($data, 'members');
        return $member_id;
    }

}