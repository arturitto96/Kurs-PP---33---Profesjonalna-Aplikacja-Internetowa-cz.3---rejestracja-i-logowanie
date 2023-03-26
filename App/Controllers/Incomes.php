<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;

/**
 * Incomes controller
 * 
 * PHP version 7.0
 */
class Incomes extends Authenticated {
    
    private $income;

    private $user;

    /**
     * Before filter - called before each action method
     * 
     * @return void
     */
    public function before() {
        parent::before();

        $this->user = Auth::getUser();
    }

    /**
     * Show the new income page
     *
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Income/new.html', ['user' => $this->user]);
    }
}