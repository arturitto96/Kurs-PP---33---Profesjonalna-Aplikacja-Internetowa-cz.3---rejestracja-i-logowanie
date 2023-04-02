<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use App\Models\Income;
use App\Models\Expense;

/**
 * Profile controller
 * 
 * PHP version 7.0
 */
class Profile extends Authenticated {

    private $user;
    /**
     * Before filter - called before each action method
     * 
     * @return void
     */
    public function before() {
        parent::before();

        $this->user = Auth::getUser();

        $this -> user -> getUserCategories();

        $this -> user -> getTodayDate();
    }
    
    /**
     * Show the profile
     * 
     * @return void
     */
    public function showAction() {
        View::renderTemplate('Profile/show.html', ['user' => $this->user]);
    }

    /**
     * Show the form for editing the profile
     * 
     * @return void
     */
    public function editAction() {
        View::renderTemplate('Profile/edit.html', ['user' => $this->user]);
    }

    /**
     * Update the profile
     * 
     * @return void
     */
    public function updateAction() {
        if ($this -> user -> updateProfile($_POST)) {
            Flash::addMessage('Zapisano zmiany');

            $this -> redirect('/profile/show');
        } else {
            View::renderTemplate('Profile/edit.html', ['user' => $this->user]);
        }
    }

    /**
     * Show the new income page
     *
     * @return void
     */
    public function newIncomeAction() {
        View::renderTemplate('Profile/newIncome.html', ['user' => $this -> user]);
    }

     /**
     * Save the income data
     *
     * @return void
     */
    public function saveIncomeAction() {
        Income::saveIncome($_POST);

        Flash::addMessage('Pomyślnie dodano przychód');

        $this -> redirect('/profile/show');

    }

    /**
     * Show the new expense page
     *
     * @return void
     */
    public function newExpenseAction() {
        View::renderTemplate('Profile/newExpense.html', ['user' => $this -> user]);
    }

    /**
     * Save the expense data
     *
     * @return void
     */
    public function saveExpenseAction() {
        Expense::saveExpense($_POST);

        Flash::addMessage('Pomyślnie dodano wydatek');

        $this -> redirect('/profile/show');

    }
}