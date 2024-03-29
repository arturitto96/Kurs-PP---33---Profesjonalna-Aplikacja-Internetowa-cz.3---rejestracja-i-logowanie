<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\SupplementaryMethods;
use \App\Models\Income;
use \App\Models\IncomeCategories;
use \App\Models\Expense;
use \App\Models\ExpenseCategories;
use \App\Models\ExpensePaymentMethods;
use \App\Models\CategoryLimit;

/**
 * Profile controller
 * 
 * PHP version 7.0
 */
class Profile extends Authenticated {

    protected $user;

    protected $incomeSummary;

    protected $expenceSummary;

    protected $todayDate;

    protected $firstDate;

    protected $startDateForSummary;

    protected $endDateForSummary;
        
    /**
     * Before filter - called before each action method
     * 
     * @return void
     */
    public function before() {
        parent::before();

        $this -> todayDate = SupplementaryMethods::getTodayDate();
        $this -> firstDate = SupplementaryMethods::getFirstDateOfThisMonth();
        $this -> user = Auth::getUser();
        $this -> user -> getUserCategories();
        $this -> user -> getShortSummary($this -> firstDate, $this -> todayDate);
    }

    

    /**
     * Get the mode of app for user 
     * 
     * @return string Mode
     */
    public function getUserMode() {
        if ($this -> user -> app_mode == true) {
            return "dark";
        } else {
            return "light";
        }
    }
    
    /**
     * Show the profile
     * 
     * @return void
     */
    public function showAction() {
        View::renderTemplate('Profile/show.html', [ 'user' => $this-> user, 'todayDate' => $this -> todayDate, 'mode' => $this -> getUserMode()]);
    }

    /**
     * Show the form for editing the user data
     * 
     * @return void
     */
    public function editUserAction() {
        View::renderTemplate('Profile/editUser.html', ['user' => $this->user, 'mode' => $this -> getUserMode()]);
    }

    /**
     * Update the user data
     * 
     * @return void
     */
    public function updateUserAction() {
        
        if ($this -> user -> updateProfile($_POST)) {
            Flash::addMessage('Zapisano zmiany');
            $this -> redirect('/profile/show');
        } else {
            View::renderTemplate('Profile/editUser.html', ['user' => $this->user, 'mode' => $this -> getUserMode()]);
        }
    }

    /**
     * Delete the user data
     * 
     * @return void
     */
    public function deleteUserAction() {
        if (isset($_POST['deleteConfirmation'])) {
            $this -> user -> deleteProfile();
            $this -> redirect('/logout');
        } else {
            View::renderTemplate('Profile/editUser.html', ['user' => $this->user]);
        }
    }

    /**
     * Toggle between dark and light mode of app
     * 
     * @return boolean mode
     */
    public function toggleModeAction() {
        $this -> user -> app_mode = !($this -> user -> app_mode);
        if ($this -> user -> updateUserMode()) {
            Flash::addMessage('Zmieniono motyw');
            $this -> redirect('/profile/show');
        } else {
            Flash::addMessage('Nie udało się zapisać zmian', FLASH::WARNING);
            $this -> redirect('/profile/show');
        }
    }
}

    