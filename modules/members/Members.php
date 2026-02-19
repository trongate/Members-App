<?php
class Members extends Trongate {

    public string $template = 'public';
    public $login_url = 'members-login';
    public $logout_url = 'members/logout';

    /**
     * Display the user's account page.
     *
     * This method:
     * 1. Retrieves the current member's data for display.
     * 2. Redirects to the login page if the member is not found.
     * 3. Renders the 'your_account' view using the member template.
     *
     * @return void
     */
    public function your_account(): void {
        $member_obj = $this->trongate_security->make_sure_allowed('members area');

        if ($member_obj === false) {
            redirect($this->login_url);
        }

        $set_password_required = $this->set_password_required($member_obj);

        $args['username'] = $member_obj->username;
        if ($set_password_required === true) {
            $info = $this->view('no_password_alert', $args, true);
        } else {
            $info = $this->view('standard_welcome_alert', $args, true);
        }

        $days_as_member = (time() - $member_obj->date_created) / 86400 | 0;

        // Prepare data array for the template
        $data = [
            'info' => $info,
            'set_password_required' => $set_password_required,
            'days_as_member' => $days_as_member,
            'first_name' => $this->encryption->decrypt($member_obj->first_name),
            'last_name' => $this->encryption->decrypt($member_obj->last_name),
            'logout_url' => $this->logout_url,
            'view_module' => 'members',
            'view_file'   => 'your_account',
            'member'      => $member_obj
        ];

        $member_template = $this->template;
        $this->templates->$member_template($data);
    }

    /**
     * Determine whether the given member is required to set a password.
     *
     * A password is considered required if the stored password property
     * is either unset or an empty string.
     *
     * @param object $member_obj An object representing the member record.
     *
     * @return bool Returns true if a password is required, otherwise false.
     */
    private function set_password_required(object $member_obj): bool {
        $stored_password = $member_obj->password ?? '';
        $password_required = ($stored_password === '') ? true : false;
        return $password_required;
    }

    /**
     * Display and process the member account update form.
     *
     * If no form submission is detected, the existing member data is
     * prepared and decrypted for display. If the form has been submitted,
     * posted data is retrieved for processing.
     *
     * @return void
     */
    public function update_account(): void {

        $member_obj = $this->trongate_security->make_sure_allowed('members area');
        
        $submit = post('submit');
        
        if ($submit === '') {
            $data = (array) $member_obj;
            $data['first_name'] = $this->encryption->decrypt($member_obj->first_name);
            $data['last_name'] = $this->encryption->decrypt($member_obj->last_name);
        } else {
            $data = $this->model->get_data_from_post();
        }
        
        $data['form_location'] = str_replace('/update_account', '/submit_update_account', current_url());
        $data['view_module'] = 'members';
        $data['view_file'] = 'update_account';
        $template_method = $this->template;
        $this->templates->$template_method($data);
    }

    /**
     * Handle submission of the account update form.
     *
     * Validates posted member data and, if successful, updates the
     * corresponding database record. On validation failure, the
     * update form is reloaded. May redirect the user depending on
     * authorisation or outcome.
     *
     * @return void
     */
    public function submit_update_account(): void {

        $member_obj = $this->trongate_security->make_sure_allowed('members area');

        if ($member_obj == false) {
            redirect($this->login_url);
        }

        $submit = post('submit', true);

        if ($submit == 'Update Account') {
            
            $this->validation->set_rules('username', 'username', 'required|min_length[3]|max_length[60]|callback_username_check');
            $this->validation->set_rules('first_name', 'first name', 'required|min_length[2]|max_length[60]');
            $this->validation->set_rules('last_name', 'last name', 'required|min_length[2]|max_length[70]');
            $this->validation->set_rules('email_address', 'email address', 'required|valid_email|max_length[70]|callback_email_check');

            $result = $this->validation->run();

            if ($result == true) {

                $data = $this->model->get_data_from_post();

                $first_name = post('first_name', true);
                $data['first_name'] = $this->encryption->encrypt($first_name);

                $last_name = post('last_name', true);
                $data['last_name'] = $this->encryption->encrypt($last_name);

                $update_id = (int) $member_obj->id;
                $this->db->update($update_id, $data, 'members');

                set_flashdata('Your account was successfully updated.');
                redirect('members/your_account');

            } else {
                //form submission error
                $this->update_account();
            }

        }

    }

    /**
     * Validate that the submitted username is valid and available.
     *
     * Ensures the username contains only alphanumeric characters and
     * is not already used by another member (excluding the current user).
     *
     * @param string $username The username to validate.
     *
     * @return bool|string Returns true if valid and available,
     *                     otherwise returns an error message string.
     */
    public function username_check(string $username): bool|string {
        block_url('members/username_check');

        // Only allow letters (a-z, A-Z) and numbers (0-9).
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            return 'The username can only contain letters and numbers';
        }

        $trongate_user_obj = $this->trongate_tokens->get_user_obj();
        $trongate_user_id = (int) ($trongate_user_obj->trongate_user_id ?? 0);

        $params = [
            'trongate_user_id' => $trongate_user_id,
            'username' => $username
        ];

        $sql = 'SELECT * FROM members WHERE username = :username AND trongate_user_id != :trongate_user_id';
        $rows = $this->db->query_bind($sql, $params, 'object');

        if (!empty($rows)) {
            return 'The username that you submitted is not available.';
        }

        return true;
    }

    /**
     * Validate that the submitted email address is available.
     *
     * Ensures the email address is not already used by another member
     * (excluding the current user).
     *
     * @param string $email_address The email address to validate.
     *
     * @return bool|string Returns true if available,
     *                     otherwise returns an error message string.
     */
    public function email_check(string $email_address): bool|string {
        block_url('members/email_check');

        $trongate_user_obj = $this->trongate_tokens->get_user_obj();
        $trongate_user_id = (int) ($trongate_user_obj->trongate_user_id ?? 0);

        $params = [
            'trongate_user_id' => $trongate_user_id,
            'email_address' => $email_address
        ];

        $sql = 'SELECT * FROM members WHERE email_address = :email_address AND trongate_user_id != :trongate_user_id';
        $rows = $this->db->query_bind($sql, $params, 'object');

        if (!empty($rows)) {
            return 'The email address that you submitted is not available.';
        }

        return true;
    }

    /**
     * Display the password update / set password form.
     *
     * Determines whether the member is setting a password
     * for the first time or updating an existing one,
     * then renders the appropriate view.
     *
     * @return void
     */
    public function update_password(): void {

        $member_obj = $this->trongate_security->make_sure_allowed('members area');
        $headline = ($member_obj->password === '') ? 'Set Your Password' : 'Update Password';

        $info1 = 'Please set a password for your account using the form below.';
        $info2 = 'Please enter your new password below then hit \'Set Password\'.';
        $info = ($member_obj->password === '') ? $info1 : $info2;

        $data = [
            'form_location' => BASE_URL.'members/submit_update_password',
            'headline' => $headline,
            'info' => $info,
            'view_module' => 'members',
            'view_file' => 'update_password'
        ];
        $template_method = $this->template;
        $this->templates->$template_method($data);
    }

    /**
     * Handle submission of the password update form.
     *
     * Validates the submitted password, hashes it,
     * updates the database record, and redirects
     * the user accordingly.
     *
     * @return void
     */
    public function submit_update_password(): void {

        $member_obj = $this->trongate_security->make_sure_allowed('members area');

        if ($member_obj == false) {
            redirect($this->login_url);
        }

        $submit = post('submit', true);

        if ($submit == 'Set Password') {
            $this->validation->set_rules('password', 'password', 'required|min_length[5]|max_length[35]|callback_password_check');
            $this->validation->set_rules('password_repeat', 'password repeat', 'required|matches[password]');

            $result = $this->validation->run();

            if ($result == true) {
                //hash the password, update it and then log the user in
                $data['password'] = $this->hash_string(post('password'));
                $this->db->update($member_obj->id, $data, 'members');

                //is this the first time that this person has logged in?
                $num_logins = $member_obj->num_logins;

                if ($num_logins<2) {
                    $flash_msg = 'Ahoy!  Welcome aboard the fun bus.  It\'s great to have you here!';
                } else {
                    $flash_msg = 'Your password was successfully updated.';
                }

                set_flashdata($flash_msg);
                redirect('members/your_account');

            } else {
                //form submission error
                $this->update_password();
            }

        }

    }

    /**
     * Generate a bcrypt hash for the given string.
     *
     * @param string $str The plain text string to hash.
     *
     * @return string The hashed string.
     */
    public function hash_string(string $str): string {
        block_url('members/hash_string');
        $hashed_string = password_hash($str, PASSWORD_BCRYPT, array(
            'cost' => 11
        ));
        return $hashed_string;
    }

    /**
     * Verify a plain text string against a hashed value.
     *
     * @param string $plain_text_str The plain text input.
     * @param string $hashed_string  The stored hashed string.
     *
     * @return bool Returns true if the hash matches, otherwise false.
     */
    public function verify_hash(string $plain_text_str, string $hashed_string): bool {
        block_url('members/verify_hash');
        $result = password_verify($plain_text_str, $hashed_string);
        return $result; //TRUE or FALSE
    }

    /**
     * Validate password strength requirements.
     *
     * Ensures the password contains at least one letter
     * and one number.
     *
     * @param string $str The password string to validate.
     *
     * @return bool|string Returns true if valid,
     *                     otherwise an error message string.
     */
    public function password_check(string $str): bool|string {

        block_url('members/password_check');

        // *** MODIFY THIS METHOD AND ADD YOUR OWN RULES, AS REQUIRED **
        if (preg_match('/[A-Za-z]/', $str) & preg_match('/\d/', $str) == 1) {
            return true;  // password contains at least one letter and one number
        } else {
            $error_msg = 'The password must contain at least one letter and one number.';
            return $error_msg;
        }
    }

    /**
     * Log a member into the system.
     *
     * Executes the model login process and determines
     * the appropriate target URL.
     *
     * @param object $member_obj The member object.
     *
     * @return object The updated member object.
     */
    public function log_user_in(object $member_obj): object {
        block_url('members/log_user_in');

        // Execute the login process
        $member_obj = $this->model->log_user_in($member_obj);
        $member_obj->target_url = ($member_obj->password === '') ? 'members/update_password' : 'members/your_account';
        return $member_obj;
    }

    /**
     * Log the current user out of the system.
     *
     * Clears stored IP address data (if applicable),
     * destroys authentication tokens, and redirects
     * to the login URL.
     *
     * @return void
     */
    public function logout(): void {
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