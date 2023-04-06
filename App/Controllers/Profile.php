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

    private $incomeSummary;

    private $expenceSummary;

    private $todayDate;

    private $firstDate;

    private $startDateForSummary;

    private $endDateForSummary;
        
    /**
     * Before filter - called before each action method
     * 
     * @return void
     */
    public function before() {
        parent::before();

        $this -> getTodayDate();
        $this -> getFirstDateOfThisMonth();
        $this -> user = Auth::getUser();
        $this -> user -> getUserCategories();
        $this -> user -> getShortSummary($this -> firstDate, $this -> todayDate);
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
        View::renderTemplate('Profile/newIncome.html', ['user' => $this -> user, 'today' => $this -> todayDate]);
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
        View::renderTemplate('Profile/newExpense.html', ['user' => $this -> user, 'today' => $this -> todayDate]);
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

    /**
     * Show the balance sheet
     *
     * @return void
     */
    public function showBalanceSheetAction() {
        if (empty($_POST)) {
            $this -> user -> getFullSummary($this -> firstDate, $this -> todayDate);
            View::RenderTemplate('Profile/balanceSheet.html', [ 'user' => $this -> user, 
                                                                'start' => $this -> firstDate, 
                                                                'end' => $this -> todayDate]);
        } else {
            $this -> user -> getFullSummary($_POST['startDate'], $_POST['endDate']);

            $this -> user -> expenseFullSummary = json_encode($this -> user -> expenseFullSummary, JSON_NUMERIC_CHECK);

            $this -> user -> expenseFullSummary = json_decode($this -> user -> expenseFullSummary, true);

            //var_dump($this -> user -> expenseFullSummary);
            
            View::RenderTemplate('Profile/balanceSheet.html', [ 'user' => $this -> user, 
                                                                'start' => $_POST['startDate'], 
                                                                'end' => $_POST['endDate']]);
        }
    }

    /**
     * Get the today date
     *
     * @return void
     */
    protected function getTodayDate() {
        $this -> todayDate = date("Y-m-d");
    }

    /**
     * Get the date of first day of this month
     *
     * @return void
     */
    protected function getFirstDateOfThisMonth() {
        $this -> firstDate = date('Y') . '-' . date('m') . '-01';
    }
}