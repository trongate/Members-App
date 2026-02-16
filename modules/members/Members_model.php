<?php
class Members_model extends Model {

    public function log_user_in($member_obj) {

    	// Generate auth token.
    	$this->module('trongate_tokens');

    	$token_data = [
    		'user_id' => (int) $member_obj->trongate_user_id,
    		'expiry_date' => time() + (86400 * 60),
    		'set_cookie' => true
    	];
        
        $this->trongate_tokens->generate_token($token_data);

        // Update 'num_logins', 'ip_address', and 'last_login'.
        $update_id = (int) $member_obj->id;
        $num_logins = (int) $member_obj->num_logins;
        $data['num_logins'] = $num_logins + 1;
        $data['ip_address'] = ip_address();
        $data['last_login'] = time();
        $this->db->update($update_id, $data, 'members');
        $member_obj->num_logins = $data['num_logins'];
        return $member_obj;
    }
    
    public function get_data_from_post() {
        $data = [
            'username' => post('username', true),
            'first_name' => post('first_name', true),
            'last_name' => post('last_name', true),
            'email_address' => post('email_address', true),
        ];
        return $data;
    }

}