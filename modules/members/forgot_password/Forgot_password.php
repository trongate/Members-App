<?php
class Forgot_password extends Trongate {

    /**
     * Display the initial forgot password screen.
     *
     * @return void
     */
    public function index(): void {
        $this->clear_old_records();

        $send_allowed = $this->is_send_allowed(ip_address());

        if ($send_allowed === false) {
            $this->view('not_allowed');
            return;
        }

        $data['username'] = post('username', true);
        $this->view('enter_username', $data);
    }

    /**
     * Handle submission of username/email.
     *
     * @return void
     */
    public function submit_username(): void {
        if ($this->is_send_allowed(ip_address()) === false) {
            $this->view('not_allowed');
            return;
        }

        $this->validation->set_rules(
            'username',
            'username/email address',
            'required|max_length[60]'
        );

        if ($this->validation->run() === true) {
            $username = post('username', true);
            $this->attempt_send_email($username);
            return;
        }

        $this->index();
    }

    /**
     * Attempt to send a reset email for a given username/email.
     *
     * @param string $username
     * @return void
     */
    private function attempt_send_email(string $username): void {

        if ($this->is_send_allowed(ip_address()) === false) {
            $this->view('not_allowed');
            return;
        }

        $member_obj = $this->model->get_member($username);

        /*
         * Important:
         * get_member() returns object|false
         * So we must check for === false (not null).
         */
        if ($member_obj === false) {
            // Fail silently to prevent user enumeration.
            redirect('members-forgot_password/done');
            return;
        }

        $first_name = $this->encryption->decrypt($member_obj->first_name);
        $last_name  = $this->encryption->decrypt($member_obj->last_name);

        $reset_url = $this->build_reset_url($member_obj);

        $data = [
            'member_name' => $first_name . ' ' . $last_name,
            'reset_url'   => $reset_url
        ];

        $email_body = $this->view('email_body', $data, true);

        if (strtolower(ENV) !== 'dev') {
            // Fire the email here.
            $email_params = [
                'to_email' => $member_obj->email_address,
                'to_name' => out($first_name).' '.out($last_name),
                'subject' => 'Your Password Reset Request',
                'body_html' => $email_body
            ];

            $result = $this->trongate_email->send($email_params);

            if (!$result) {
                echo 'Failed to send email';
                die();
            }

        }

        redirect('members-forgot_password/done');
    }

    /**
     * Display reset confirmation screen based on token validity.
     *
     * @return void
     */
    public function reset(): void {

        $data['token'] = segment(3);

        $token_valid = $this->model->is_token_valid($data['token']);

        $view_file = ($token_valid === true)
            ? 'confirm_reset'
            : 'token_invalid';

        $this->view($view_file, $data);
    }

    /**
     * Handle submission of confirmed reset.
     *
     * @return void
     */
    public function submit_confirm_reset(): void {

        $token = post('token');

        if ($this->model->is_token_valid($token) === false) {
            $this->view('token_invalid');
            return;
        }

        $record_obj = $this->db->get_one_where(
            'token',
            $token,
            'password_reset_tokens'
        );

        $member_id  = (int) $record_obj->member_id;
        $member_obj = $this->db->get_where($member_id, 'members');

        $this->members->log_user_in($member_obj);

        // Delete the used token.
        $update_id = (int) $record_obj->id;
        $this->db->delete($update_id, 'password_reset_tokens');

        redirect('members/update_password');
    }

    /**
     * Build reset URL and persist token record.
     *
     * @param object $member_obj
     * @return string
     */
    private function build_reset_url(object $member_obj): string {

        $data = [
            'ip_address'    => ip_address(),
            'email_address' => $member_obj->email_address,
            'date_created'  => time(),
            'token'         => make_rand_str(32),
            'member_id'     => (int) $member_obj->id
        ];

        $this->db->insert($data, 'password_reset_tokens');

        return BASE_URL . 'members-forgot_password/reset/' . $data['token'];
    }

    /**
     * Determine whether password reset sending is allowed for an IP address.
     *
     * Limits to 3 reset requests per IP.
     *
     * @param string $ip_address
     * @return bool
     */
    private function is_send_allowed(string $ip_address): bool {

        $params['ip_address'] = $ip_address;

        $sql = 'SELECT * FROM password_reset_tokens
                WHERE ip_address = :ip_address';

        $rows = $this->db->query_bind($sql, $params, 'object');

        return (count($rows) < 3);
    }

    /**
     * Display rate-limit not allowed page.
     *
     * @return void
     */
    public function not_allowed(): void {
        $this->view('not_allowed');
    }

    /**
     * Display confirmation that reset email has been sent.
     *
     * @return void
     */
    public function done(): void {
        $data['username'] = 'whatever';
        $this->view('done', $data);
    }

    /**
     * Remove expired password reset tokens (older than 24 hours).
     * If table becomes empty, reset auto-increment.
     *
     * @return void
     */
    private function clear_old_records(): void {

        $ancient_history = time() - 86400;

        $sql = 'DELETE FROM password_reset_tokens
                WHERE date_created < ' . $ancient_history;

        $this->db->query($sql);

        $rows = $this->db->get('id', 'password_reset_tokens');

        if (empty($rows)) {
            $this->db->query('TRUNCATE password_reset_tokens');
        }
    }
}