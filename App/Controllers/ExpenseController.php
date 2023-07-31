<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\SupplementaryMethods;
use \App\Models\Expense;

/**
 * Expense controller
 * 
 * PHP version 7.0
 */
class ExpenseController extends Profile {

    private $user;

    private $todayDate;

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
     * Show the new expense page
     *
     * @return void
     */
    public function newExpenseAction() {
        View::renderTemplate('Profile/newExpense.html', ['user' => $this -> user, 'today' => $this -> todayDate, 'mode' => $this -> getUserMode()]);
    }

    /**
     * Save the expense data
     *
     * @return void
     */
    public function saveExpenseAction() {
        if (Expense::saveExpense($_POST)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/Profile/show');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/ExpenseController/newExpense');
        }
    }

}