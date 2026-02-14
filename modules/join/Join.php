<?php
/**
 * Join Controller
 *
 * Handles user registration and membership creation functionality.
 */
class Join extends Trongate {

    /**
     * Display the registration form
     *
     * @return void
     */
    public function index(): void {
        $data = $this->model->get_data_from_post();
        $data['view_module'] = 'join';
        $data['view_file'] = 'join';
        $this->templates->public($data);
    }

    /**
     * Process registration form submission
     *
     * Validates user input and creates a new member record if validation passes.
     *
     * @return void
     */
    public function submit(): void {
        $this->validation->set_rules('username', 'username', 'required|min_length[3]|max_length[60]|callback_username_check');
        $this->validation->set_rules('first_name', 'first name', 'required|max_length[60]');
        $this->validation->set_rules('last_name', 'last name', 'required|max_length[70]');
        $this->validation->set_rules('email_address', 'email address', 'required|valid_email|max_length[70]|callback_email_check');

        $result = $this->validation->run();  // return true or false.

        if ($result === true) {
            // Fetch the posted data.
            $data = $this->model->get_data_from_post();

            // Create new member record.
            $member_id = $this->model->create_new_member_record($data);

            // Send an activate account email.
            $this->send_activate_account_email($member_id);

            // Redirect the user to a 'check your email' page.
            redirect('join/check_your_email');

        } else {
            // Present the form again.
            $this->index();
        }
    }

    private function send_activate_account_email($member_id) {
        // Fetch the first_name, last_name and email_address
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
            // We are live!  Send the email!
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

    public function check_your_email() {
        $data = [
            'view_module' => 'join',
            'view_file' => 'check_your_email'
        ];

        $this->templates->public($data);
    }

    public function activate() {
        $user_token = segment(3);

        $member_obj = $this->model->attempt_activate_account($user_token);

        if ($member_obj === false) {
            $data = [
                'view_module' => 'join',
                'view_file' => 'invalid_activation_code'
            ];

            $this->templates->public($data);
        } else {
            $member_obj = $this->members->log_user_in($member_obj);
            redirect($member_obj->target_url);
        }
    }

    /**
     * Validate username availability
     *
     * Callback method for form validation to check if username is already taken.
     *
     * @param string $username The username to check
     * @return bool|string Returns true if available, error message if taken
     */
    public function username_check(string $username): bool|string {
        // returns true or a string.
        // Make sure the submitted username is not already taken.
        $result = $this->model->username_check($username);
        return $result;
    }

    /**
     * Validate email address availability
     *
     * Callback method for form validation to check if email address is already taken.
     *
     * @param string $email_address The email address to check
     * @return bool|string Returns true if available, error message if taken
     */
    public function email_check(string $email_address): bool|string {
        // returns true or a string.
        // Make sure the submitted email address is not already taken.
        $result = $this->model->email_check($email_address);
        return $result;
    }

}