<?php
/**
 * Join Controller
 *
 * Handles user registration and membership creation functionality.
 */
class Join extends Trongate {

    /**
     * Display the registration form.
     *
     * Ensures registration is permitted from the current IP address
     * before rendering the join view.
     *
     * @return void
     */
    public function index(): void {
        if (!$this->is_registration_allowed()) {
            $this->registration_not_allowed();
            return;
        }
        
        $data = $this->model->get_data_from_post();
        $data['view_module'] = 'join';
        $data['view_file'] = 'join';
        $this->templates->public($data);
    }

    /**
     * Process registration form submission.
     *
     * Validates input, creates a new member record if valid,
     * sends an activation email, and redirects accordingly.
     *
     * @return void
     */
    public function submit(): void {
        if (!$this->is_registration_allowed()) {
            $this->registration_not_allowed();
            return;
        }

        $this->validation->set_rules('username', 'username', 'required|min_length[3]|max_length[60]|callback_username_check');
        $this->validation->set_rules('first_name', 'first name', 'required|max_length[60]');
        $this->validation->set_rules('last_name', 'last name', 'required|max_length[70]');
        $this->validation->set_rules('email_address', 'email address', 'required|valid_email|max_length[70]|callback_email_check');

        $result = $this->validation->run();

        if ($result === true) {
            $data = $this->model->get_data_from_post();
            $member_id = $this->model->create_new_member_record($data);
            $this->send_activate_account_email($member_id);
            redirect('join/check_your_email');
        } else {
            $this->index();
        }
    }

    /**
     * Send account activation email to newly registered member.
     *
     * @param int $member_id The ID of the newly created member.
     *
     * @return void
     */
    private function send_activate_account_email(int $member_id): void {
        $member_obj = $this->model->get_member_obj($member_id);

        $first_name = $this->encryption->decrypt($member_obj->first_name);
        $last_name = $this->encryption->decrypt($member_obj->last_name);

        $activation_url = BASE_URL.'join/activate/'.$member_obj->user_token;

        $data = [
            'email_address' => $member_obj->email_address,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'activation_url' => $activation_url
        ];

        $body_html = $this->view('activate_account_email', $data, true);

        if (strtolower(ENV) !== 'dev') {
            $email_params = [
                'to_email' => $data['email_address'],
                'to_name' => out($data['first_name']).' '.out($data['last_name']),
                'subject' => 'Your Trongate Account Confirmation',
                'body_html' => $body_html
            ];

            $result = $this->trongate_email->send($email_params);

            if (!$result) {
                echo 'Failed to send email';
                die();
            }
        }
    }

    /**
     * Display the "check your email" page.
     *
     * @return void
     */
    public function check_your_email(): void {
        $data = [
            'view_module' => 'join',
            'view_file' => 'check_your_email'
        ];

        $this->templates->public($data);
    }

    /**
     * Display account activation confirmation screen.
     *
     * Retrieves the user token from the URL, validates it,
     * and either shows the activation confirmation form
     * or an invalid activation code page.
     *
     * URL format: /join/activate/{user_token}
     *
     * @return void
     */
    public function activate(): void {

        $data['user_token'] = segment(3);

        $token_valid = $this->model->is_token_valid($data['user_token']);

        if ($token_valid === false) {

            $data = [
                'view_module' => 'join',
                'view_file'   => 'invalid_activation_code'
            ];

            $this->templates->public($data);
            return;
        }

        /*
         * The confirm_activate view expects:
         * $user_token
         */
        $this->view('confirm_activate', $data);
    }

    /**
     * Process activation confirmation submission.
     *
     * Attempts to activate the member account using the submitted
     * user token. If successful, logs the member in and redirects
     * to their target URL. Otherwise displays an invalid activation page.
     *
     * @return void
     */
    public function submit_activate_account(): void {

        $user_token = post('user_token');

        /*
         * attempt_activate_account() returns object|false
         */
        $member_obj = $this->model->attempt_activate_account($user_token);

        if ($member_obj === false) {

            $data = [
                'view_module' => 'join',
                'view_file'   => 'invalid_activation_code'
            ];

            $this->templates->public($data);
            return;
        }

        /*
         * log_user_in() is expected to return the authenticated
         * member object (containing target_url).
         */
        $member_obj = $this->members->log_user_in($member_obj);

        redirect($member_obj->target_url);
    }

    /**
     * Determine whether registration is allowed for the current IP address.
     *
     * Registration is denied if an unconfirmed account was created
     * from the same IP within the last 24 hours.
     *
     * @return bool True if allowed, otherwise false.
     */
    private function is_registration_allowed(): bool {
        $ip_address = ip_address();
        
        $sql = 'SELECT COUNT(*) as count FROM members 
                WHERE ip_address = :ip_address 
                AND confirmed = 0 
                AND date_created > :min_time';
        
        $params = [
            'ip_address' => $ip_address,
            'min_time' => time() - 86400
        ];
        
        $rows = $this->db->query_bind($sql, $params, 'object');
        return ($rows[0]->count == 0);
    }

    /**
     * Display registration restriction page.
     *
     * @return void
     */
    private function registration_not_allowed(): void {
        $data = [
            'view_module' => 'join',
            'view_file' => 'registration_not_allowed'
        ];
        
        $this->templates->public($data);
    }

    /**
     * Validate username availability.
     *
     * @param string $username The username to validate.
     *
     * @return bool|string True if available, otherwise error message.
     */
    public function username_check(string $username): bool|string {
        return $this->model->username_check($username);
    }

    /**
     * Validate email address availability.
     *
     * @param string $email_address The email address to validate.
     *
     * @return bool|string True if available, otherwise error message.
     */
    public function email_check(string $email_address): bool|string {
        return $this->model->email_check($email_address);
    }

}