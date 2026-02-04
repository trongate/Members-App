<?php
class Join extends Trongate {

    public function index() {
        $data = $this->model->get_data_from_post();
        $data['view_module'] = 'join';
        $data['view_file'] = 'join';
        $this->templates->public($data);
    }

    public function submit() {
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

    public function username_check($username) {
        // returns true or a string.
        // Make sure the submitted username is not already taken.
        $result = $this->model->username_check($username);
        return $result;
    }

    public function email_check($email_address) {
        // returns true or a string.
        // Make sure the submitted email address is not already taken.
        $result = $this->model->email_check($email_address);
        return $result;
    }

}