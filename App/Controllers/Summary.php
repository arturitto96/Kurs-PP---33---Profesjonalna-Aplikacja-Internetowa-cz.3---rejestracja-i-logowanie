<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\SupplementaryMethods;

/**
 * Summary controller
 * 
 * PHP version 7.0
 */
class Summary extends Profile { 
    private $user;

    private $todayDate;

    private $firstDate;
    
    public function before() {
        parent::before();

        $this -> user = Auth::getUser();
        $this -> todayDate = SupplementaryMethods::getTodayDate();
        $this -> firstDate = SupplementaryMethods::getFirstDateOfThisMonth();
        // $this -> user -> getUserCategories();
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
                                                                'end' => $this -> todayDate,
                                                                'today' => $this -> todayDate, 
                                                                'mode' => $this -> getUserMode()]);
        } else {
            $this -> user -> getFullSummary($_POST['startDate'], $_POST['endDate']);    
            View::RenderTemplate('Profile/balanceSheet.html', [ 'user' => $this -> user, 
                                                                'start' => $_POST['startDate'], 
                                                                'end' => $_POST['endDate'],
                                                                'today' => $this -> todayDate, 
                                                                'mode' => $this -> getUserMode()]);
        }
    }
}