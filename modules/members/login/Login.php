<?php
class Login extends Trongate {

    public function index() {
        // Make sure this person is allowed to attempt to login.
        $this->rate_limiter->make_sure_login_attempt_allowed();

        $this->view('login');
    }

    public function submit_login() {
        // Make sure this person is allowed to attempt to login.
        $this->rate_limiter->make_sure_login_attempt_allowed();

        $this->validation->set_rules('username', 'username/email', 'required|callback_login_check');
        $this->validation->set_rules('password', 'password', 'required');

        $result = $this->validation->run();

        if ($result === true) {

            // Clears any existing login rate limiter records for this user.
            $this->rate_limiter->login_success();

            $username = post('username', true);
            $member_obj = $this->model->attempt_find_matching_user($username);
            $member_obj = $this->members->log_user_in($member_obj);
            redirect($member_obj->target_url);

        } else {

            // Register a failed login attempt for the current IP address.
            $this->rate_limiter->register_failed_login_attempt();

            // Present the login page (again).
            $this->index();
        }
    }

    public function login_check($username) {
        $password = post('password');
        $login_result = $this->model->login_check($username, $password);
        return $login_result; // Will be either true (bool) or an error msg (string)
    }

}