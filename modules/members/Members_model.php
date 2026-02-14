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

        // Update 'num_logins'.
        $update_id = (int) $member_obj->id;
        $num_logins = (int) $member_obj->num_logins;
        $data['num_logins'] = $num_logins + 1;
        $this->db->update($update_id, $data, 'members');
        $member_obj->num_logins = $data['num_logins'];
        return $member_obj;
    }

}