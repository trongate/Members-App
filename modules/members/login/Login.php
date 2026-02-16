<?php
class Login extends Trongate {

    /**
     * Display the login page.
     *
     * Ensures login attempts are permitted via the rate limiter
     * and clears stale member IP addresses before rendering the view.
     *
     * @return void
     */
    public function index(): void {
        // Make sure this person is allowed to attempt to login.
        $this->rate_limiter->ensure_attempt_allowed();

        // Clear IP addresses for members who haven't logged in for 24 hours.
        $this->members->clear_old_ip_addresses();

        $this->view('login');
    }

    /**
     * Process login form submission.
     *
     * Validates credentials, logs the user in if valid,
     * otherwise registers a failed attempt and redisplays the form.
     *
     * @return void
     */
    public function submit_login(): void {
        // Make sure this person is allowed to attempt to login.
        $this->rate_limiter->ensure_attempt_allowed();

        $this->validation->set_rules('username', 'username/email', 'required|callback_login_check');
        $this->validation->set_rules('password', 'password', 'required');

        $result = $this->validation->run();

        if ($result === true) {

            // Clears any existing login rate limiter records for this user.
            $this->rate_limiter->after_success();

            $username = post('username', true);
            $member_obj = $this->model->attempt_find_matching_user($username);
            $member_obj = $this->members->log_user_in($member_obj);
            redirect($member_obj->target_url);

        } else {

            // Register a failed login attempt for the current IP address.
            $this->rate_limiter->register_failed_attempt();

            // Present the login page (again).
            $this->index();
        }
    }

    /**
     * Validation callback to verify login credentials.
     *
     * @param string $username Submitted username or email address.
     *
     * @return bool|string Returns true if valid,
     *                     otherwise an error message string.
     */
    public function login_check(string $username): bool|string {
        $password = post('password');
        $login_result = $this->model->login_check($username, $password);
        return $login_result;
    }

}