<?php
class Members extends Trongate {

    public $login_url = 'members-login';
    public $logout_url = 'members/logout';

    public function update_password() {
        echo 'Display update password page (later!)';
    }

    public function log_user_in($member_obj) {
        block_url('members/log_user_in');

        // Execute the login process
        $member_obj = $this->model->log_user_in($member_obj);
        $member_obj->target_url = ($member_obj->password === '') ? 'members/update_password' : 'dashboard';
        return $member_obj;
    }

    public function logout() {
        $this->trongate_tokens->destroy();
        redirect($this->login_url);
    }

    public function log_user_out() {
        block_url('members/log_user_out');
    }

    public function make_sure_allowed($scenario, $params) {

        $trongate_user_obj = $this->trongate_tokens->get_user_obj();

        if ($trongate_user_obj === false) {
            redirect($this->login_url);
        }

    }

}