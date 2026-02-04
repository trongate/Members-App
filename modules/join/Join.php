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
            echo 'Welcome and thanks for joining!';
        } else {
            // Present the form again.
            $this->index();
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