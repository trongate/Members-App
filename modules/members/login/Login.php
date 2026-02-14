<?php
class Login extends Trongate {

    public function index() {
        $this->view('login');
    }

    public function submit_login() {
        $this->validation->set_rules('username', 'username/email', 'required|callback_login_check');
        $this->validation->set_rules('password', 'password', 'required');

        $result = $this->validation->run();

        if ($result === true) {
            
            $username = post('username', true);
            $member_obj = $this->model->attempt_find_matching_user($username);
            $member_obj = $this->members->log_user_in($member_obj);
            redirect($member_obj->target_url);

        } else {
            $this->index();
        }
    }

    public function login_check($username) {
        $password = post('password');
        $login_result = $this->model->login_check($username, $password);
        return $login_result; // Will be either true (bool) or an error msg (string)
    }

}