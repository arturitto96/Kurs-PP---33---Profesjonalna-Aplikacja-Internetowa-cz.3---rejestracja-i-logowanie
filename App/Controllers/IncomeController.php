<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\SupplementaryMethods;
use \App\Models\Income;

/**
 * Income controller
 * 
 * PHP version 7.0
 */
class IncomeController extends Profile {

    /**
     * Before filter - called before each action method
     * 
     * @return void
     */
    public function before() {
        parent::before();

        $this -> user = Auth::getUser();
        $this -> user -> getUserCategories();
        $this -> todayDate = SupplementaryMethods::getTodayDate();
    }

    /**
     * Show the new income page
     *
     * @return void
     */
    public function newIncomeAction() {
        View::renderTemplate('Profile/newIncome.html', ['user' => $this -> user, 'today' => $this -> todayDate, 'mode' => $this -> getUserMode()]);
    }

    /**
     * Save the new income data
     *
     * @return void
     */
    public function saveIncomeAction() {
        if (Income::saveIncome($_POST)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/profile/show');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/IncomeController/newIncome');
        }
    }
}