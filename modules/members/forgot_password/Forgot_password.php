<?php
class Forgot_password extends Trongate {

    public function index() {
        $this->clear_old_records();
        $send_allowed = $this->is_send_allowed(ip_address());

        if ($send_allowed === false) {
            $this->view('not_allowed');
        } else {
            $data['username'] = post('username', true);
            $this->view('enter_username', $data);
        }

    }

    public function submit_username() {
        $send_allowed = $this->is_send_allowed(ip_address());
        if ($send_allowed === false) {
            $this->view('not_allowed');
            return;
        }

    	$this->validation->set_rules('username', 'username/email address', 'required|max_length[60]');
    	$result = $this->validation->run();

    	if ($result === true) {
    		$username = post('username', true);
    		$this->attempt_send_email($username);
    	} else {
    		$this->index();
    	}
    }

    private function attempt_send_email($username) {
        $send_allowed = $this->is_send_allowed(ip_address());

        if ($send_allowed === false) {
            $this->view('not_allowed');
            return;
        }
        
    	$member_obj = $this->model->get_member($username);

    	if ($send_allowed === true) {
    		// Send the email
    		$first_name = $this->encryption->decrypt($member_obj->first_name);
    		$last_name = $this->encryption->decrypt($member_obj->last_name);
    		$reset_url = $this->build_reset_url($member_obj);
    		$data = [
    			'member_name' => $first_name.' '.$last_name,
    			'reset_url' => $reset_url
    		];
    		$email_body = $this->view('email_body', $data, true);
    		
    		if (strtolower(ENV) !== 'dev') {
    			// Fire the email!
    		}

    		redirect('members-forgot_password/done');
    	}

    }

    public function reset() {
        $data['token'] = segment(3);
        $token_valid = $this->model->is_token_valid($data['token']);

        $view_file = ($token_valid === true) ? 'confirm_reset' : 'token_invalid';
        $this->view($view_file, $data);
    }

    public function submit_confirm_reset() {
        $token = post('token');
        $token_valid = $this->model->is_token_valid($token);

        if ($token_valid === false) {
            $this->view('token_invalid');
        } else {
            $record_obj = $this->db->get_one_where('token', $token, 'password_reset_tokens');
            $member_id = (int) $record_obj->member_id;
            $member_obj = $this->db->get_where($member_id, 'members');
            $this->members->log_user_in($member_obj);

            // Delete the record with the token.
            $update_id = (int) $record_obj->id;
            $this->db->delete($update_id, 'password_reset_tokens');

            // Send the user to the 'update password' page.
            redirect('members/update_password');
        }
    }

    private function build_reset_url($member_obj) {

        $data = [
            'ip_address' => ip_address(),
            'email_address' => $member_obj->email_address,
            'date_created' => time(),
            'token' => make_rand_str(32),
            'member_id' => (int) $member_obj->id
        ];

        $this->db->insert($data, 'password_reset_tokens');
    	$reset_url = BASE_URL.'members-forgot_password/reset/'.$data['token'];
    	return $reset_url;
    }

    private function is_send_allowed($ip_address) {

        $params['ip_address'] = $ip_address;
        $sql = 'SELECT * FROM password_reset_tokens WHERE ip_address = :ip_address';
        $rows = $this->db->query_bind($sql, $params, 'object');
        $num_rows = count($rows);

        if ($num_rows >= 3) {
            return false;
        } else {
            return true;
        }

    }

    public function not_allowed() {
    	$this->view('not_allowed');
    }

    public function done() {
    	$data['username'] = 'whatever';
    	$this->view('done', $data);
    }

    private function clear_old_records() {
        $ancient_history = time() - 86400;
        $sql = 'DELETE FROM password_reset_tokens WHERE date_created < '.$ancient_history;
        $this->db->query($sql);

        $rows = $this->db->get('id', 'password_reset_tokens');
        if (empty($rows)) {
            $sql2 = 'TRUNCATE password_reset_tokens';
            $this->db->query($sql2);
        }
    }

}