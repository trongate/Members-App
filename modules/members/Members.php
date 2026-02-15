<?php
class Members extends Trongate {

    public string $template = 'public';
    public $login_url = 'members-login';
    public $logout_url = 'members/logout';

    /**
     * Password form
     */
    public function update_password(): void {

        $member_obj = $this->trongate_security->make_sure_allowed('members area');

        $data = [
            'form_location' => BASE_URL.'members/submit_update_password',
            'page_title' => 'Update Password',
            'view_module' => 'members',
            'view_file' => 'update_password'
        ];
        $template_method = $this->template;
        $this->templates->$template_method($data);
    }

    public function log_user_in($member_obj) {
        block_url('members/log_user_in');

        // Execute the login process
        $member_obj = $this->model->log_user_in($member_obj);
        $member_obj->target_url = ($member_obj->password === '') ? 'members/update_password' : 'dashboard';
        return $member_obj;
    }

    public function logout() {
        // Get current user's member ID before destroying token
        $trongate_user_obj = $this->trongate_tokens->get_user_obj();

        if ($trongate_user_obj !== false) {
            // Find the member record for this user
            $member_obj = $this->db->get_one_where('trongate_user_id', $trongate_user_obj->id, 'members');

            if ($member_obj !== false) {
                // Clear the IP address
                $update_id = (int) $member_obj->id;
                $data['ip_address'] = '';
                $this->db->update($update_id, $data, 'members');
            }
        }

        $this->trongate_tokens->destroy();
        redirect($this->login_url);
    }

    public function log_user_out() {
        block_url('members/log_user_out');
    }

    /**
     * Clear IP addresses for members who haven't logged in for 24 hours
     *
     * This method finds all members records where last_login is greater than 0
     * and clears the ip_address if the login happened more than 24 hours ago.
     * This helps protect user privacy by not storing IP addresses unnecessarily.
     *
     * @return void
     */
    public function clear_old_ip_addresses(): void {
        $twenty_four_hours_ago = time() - 86400; // 24 hours in seconds

        $sql = 'UPDATE members
                SET ip_address = ""
                WHERE last_login > 0
                AND last_login < :cutoff_time';

        $params = ['cutoff_time' => $twenty_four_hours_ago];
        $this->db->query_bind($sql, $params);
    }

    /**
     * Verifies that the current user is authorised for the given scenario.
     *
     * If the user is not authenticated, execution is terminated via redirect().
     * When this method returns, it will always return a merged user/member object.
     *
     * @param string $scenario The authorisation scenario identifier.
     * @param array  $params   Additional parameters for the scenario.
     *
     * @return object Merged object containing properties from the Trongate
     *                user and associated member record.
     */
    public function make_sure_allowed(string $scenario, array $params): object {
        $trongate_user_obj = $this->trongate_tokens->get_user_obj();

        if ($trongate_user_obj === false) {
            redirect($this->login_url);
        }

        $member_obj = $this->db->get_one_where(
            'trongate_user_id',
            $trongate_user_obj->trongate_user_id,
            'members'
        );

        return (object) array_merge(
            (array) $trongate_user_obj,
            (array) $member_obj
        );
    }

}