<?php
class Members extends Trongate {

    public function make_sure_allowed($scenario, $params) {

        $trongate_user_obj = $this->trongate_tokens->get_user_obj();

        if ($trongate_user_obj === false) {
            redirect('members-login');
        }

    }

}