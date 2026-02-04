<?php
class Dashboard extends Trongate {

    public function index() {

        // Make sure user is logged in as a member.
        $this->trongate_security->make_sure_allowed('members area');

        echo 'private members area';
    }

}