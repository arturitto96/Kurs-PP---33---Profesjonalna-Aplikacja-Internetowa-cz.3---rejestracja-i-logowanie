<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Controllers\Profile;
use \App\Flash;
use \App\Models\CategoryLimit;

/**
 * CategoryLimitController controller
 * 
 * PHP version 7.0
 */
class CategoryLimitController extends Profile {
// class CategoryLimitController extends Authenticated
    private $user;

    private $todayDate;

    /**
     * Before filter - called before each action method
     * 
     * @return void
     */
    public function before() {
        parent::before();

        $this -> getTodayDate();
        $this -> user = Auth::getUser();
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
     * Gets the limit value for category
     * 
     * @return float Limit value
     */
    public function categoryLimitAction() {
        $categoryName = $this -> route_params['category'];

        echo json_encode(CategoryLimit::getCategoryLimit($categoryName, $this -> user -> id), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Gets the summary for selected category in this month
     * 
     * @return float Summary
     */
    public function categoryLimitSummaryAction() {
        $categoryName = $this -> route_params['category'];
        $month = substr($this -> todayDate, 4, -2);

        echo json_encode(CategoryLimit::getCategorySummary($categoryName, $month, $this -> user -> id), JSON_UNESCAPED_UNICODE);
    }

/**
     * Gets the current state of limit for category
     * 
     * @return boolean Limit state
     */
    public function categoryLimitStateAction() {
        $categoryName = $this -> route_params['category'];

        echo json_encode(CategoryLimit::getCategoryLimitState($categoryName, $this -> user -> id), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Activates or deactivates limit for selected category
     * 
     * @return boolean Returns 1 if activate, 0 otherwise 
     */
    public function activateLimitAction() {
        if (CategoryLimit::setLimitState($_POST['categoryName'], $_POST['currentLimitState'], $this -> user -> id)) {
            Flash::addMessage('Pomyślnie zaaktualizowano stan limitu');
            $this -> redirect('/profile/editExpensesCategory');
        } else {
            Flash::addMessage('Nie udało się zapisać zmian', FLASH::WARNING);
            $this -> redirect('/profile/editExpensesCategory');
        }
    }
}