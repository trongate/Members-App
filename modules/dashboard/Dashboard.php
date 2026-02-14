<?php
class Dashboard extends Trongate {

    public function index() {

        // Make sure user is logged in as a member.
        $this->trongate_security->make_sure_allowed('members area');

        echo 'private members area<br><br>';

        $logout_url = $this->members->logout_url;

        echo anchor($logout_url, 'Log Out');

    }

}