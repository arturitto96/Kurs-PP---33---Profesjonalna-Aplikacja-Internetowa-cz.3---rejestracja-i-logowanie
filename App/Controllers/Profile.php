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
     * Show the form for editing the user data
     * 
     * @return void
     */
    public function editUserAction() {
        View::renderTemplate('Profile/editUser.html', ['user' => $this->user]);
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
            View::renderTemplate('Profile/editUser.html', ['user' => $this->user]);
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
                                                                'end' => $this -> todayDate,
                                                                'today' => $this -> todayDate]);
        } else {
            $this -> user -> getFullSummary($_POST['startDate'], $_POST['endDate']);    
            View::RenderTemplate('Profile/balanceSheet.html', [ 'user' => $this -> user, 
                                                                'start' => $_POST['startDate'], 
                                                                'end' => $_POST['endDate'],
                                                                'today' => $this -> todayDate]);
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

    /**
     * Show the form for editing the user incomes categories
     * 
     * @return void
     */
    public function editIncomesCategoryAction() {
        View::renderTemplate('Profile/editIncomes.html', ['user' => $this -> user]);
    }


    /**
     * Show the form for editing the user expenses categories
     * 
     * @return void
     */
    public function editExpensesCategoryAction() {
        View::renderTemplate('Profile/editExpenses.html', ['user' => $this -> user]);
    }

    /**
     * Show the form for editing the user payment methods
     * 
     * @return void
     */
    public function editPaymentMethodsAction() {
        View::renderTemplate('Profile/editPayment.html', ['user' => $this -> user]);
    }

    /**
     * Update selected category
     * 
     * @return void
     */
    public function updateCategoryAction() {
        if ($_POST['type'] == 'income') {
            if (Income::editCategoryName($_POST, $this -> user -> id)) {
                Flash::addMessage('Pomyślnie zapisano zmianę nazwy kategorii');
                $this -> redirect('/profile/editIncomesCategory');
            } else {
                Flash::addMessage('Nie udało się zapisać zmiany', FLASH::WARNING);
                $this -> redirect('/profile/editIncomesCategory');
            }
        } else if ($_POST['type'] == 'expense') {
            if (Expense::editCategory($_POST, $this -> user -> id)) {
                Flash::addMessage('Pomyślnie zapisano zmianę nazwy kategorii');
                $this -> redirect('/profile/editExpensesCategory');
            } else {
                Flash::addMessage('Nie udało się zapisać zmiany', FLASH::WARNING);
                $this -> redirect('/profile/editExpensesCategory');
            }
        } else {
            Flash::addMessage('Coś poszło nie tak');
            $this -> redirect('/profile/show');
        }
    }

    /**
     * Save new name for selected payment method
     * 
     * @return void
     */
    public function saveNewPaymentNameAction() {
        if ($_POST['type'] == 'payment') {
            if (Expense::editPaymentName($_POST, $this -> user -> id)) {
                Flash::addMessage('Pomyślnie zapisano zmianę nazwy metody płatności');
                $this -> redirect('/profile/editPaymentMethods');
            } else {
                Flash::addMessage('Nie udało się zapisać zmiany', FLASH::WARNING);
                $this -> redirect('/profile/editPaymentMethods');
            }
        } else {
            Flash::addMessage('Coś poszło nie tak', FLASH::WARNING);
            $this -> redirect('/profile/show');
        }
    }

     /**
     * Delete selected category
     * 
     * @return void
     */
    public function deleteCategoryAction() {
        if ($_POST['type'] == 'income') {
            if (Income::deleteCategory($_POST, $this -> user -> id)) {
                Flash::addMessage('Pomyślnie usunięto kategorię');
                $this -> redirect('/profile/editIncomesCategory');
            } else {
                Flash::addMessage('Nie udało się usunąć wybranej kategorii', FLASH::WARNING);
                $this -> redirect('/profile/editIncomesCategory');
            }
            
        } else if ($_POST['type'] == 'expense') {
            if (Expense::deleteCategory($_POST, $this -> user -> id)) {
                Flash::addMessage('Pomyślnie usunięto kategorię');
                $this -> redirect('/profile/editExpensesCategory');
            } else {
                Flash::addMessage('Nie udało się usunąć wybranej kategorii', FLASH::WARNING);
                $this -> redirect('/profile/editExpensesCategory');
            }
        } else {
            Flash::addMessage('Coś poszło nie tak', FLASH::WARNING);
            $this -> redirect('/profile/show');
        }
    }

    /**
     * Delete selected payment method
     * 
     * @return void
     */
    public function deletePaymentAction() {
        if ($_POST['type'] == 'payment') {
            if (Expense::deletePayment($_POST, $this -> user -> id)) {
                Flash::addMessage('Pomyślnie usunięto metodę płatności');
                $this -> redirect('/profile/editPaymentMethods');
            } else {
                Flash::addMessage('Nie udało się usunąć wybranej kategorii', FLASH::WARNING);
                $this -> redirect('/profile/editPaymentMethods');
            }
        } else {
            Flash::addMessage('Coś poszło nie tak', FLASH::WARNING);
            $this -> redirect('/profile/show');
        }
    }

    /**
     * Save new category
     * 
     * @return void
     */
    public function saveNewCategoryAction() {
        if ($_POST['type'] == 'income') {
            if (Income::saveNewCategory($_POST, $this -> user -> id)) {
                Flash::addMessage('Pomyślnie zapisano nową kategorię');
                $this -> redirect('/profile/editIncomesCategory');
            } else {
                Flash::addMessage('Nie udało się zapisać zmian', FLASH::WARNING);
                $this -> redirect('/profile/editIncomesCategory');
            }
        } else if ($_POST['type'] == 'expense') {
            if (Expense::saveNewCategory($_POST, $this -> user -> id)) {
                Flash::addMessage('Pomyślnie zapisano nową kategorię');
                $this -> redirect('/profile/editExpensesCategory');
            } else {
                Flash::addMessage('Nie udało się zapisać zmian', FLASH::WARNING);
                $this -> redirect('/profile/editExpensesCategory');
            }
        } else {
            Flash::addMessage('Coś poszło nie tak', FLASH::WARNING);
            $this -> redirect('/profile/show');
        }
    }

    /**
     * Save new payment method
     * 
     * @return void
     */
    public function saveNewPaymentAction() {
        if ($_POST['type'] == 'payment') {
            if (Expense::saveNewPayment($_POST, $this -> user -> id)) {
                Flash::addMessage('Pomyślnie zapisano nową metodę płatności');
                $this -> redirect('/profile/editPaymentMethods');
            } else {
                Flash::addMessage('Nie udało się zapisać zmian', FLASH::WARNING);
                $this -> redirect('/profile/editPaymentMethods');
            }
        } else {
            Flash::addMessage('Coś poszło nie tak', FLASH::WARNING);
            $this -> redirect('/profile/show');
        }
    }

    /**
     * Gets the limit value for category
     * 
     * @return float Limit value
     */
    public function categoryLimitAction() {
        $categoryName = $this -> route_params['category'];

        echo json_encode(Expense::getCategoryLimit($categoryName, $this -> user -> id), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Gets the summary for selected category in this month
     * 
     * @return float Summary
     */
    public function categoryLimitSummaryAction() {
        $categoryName = $this -> route_params['category'];
        $month = substr($this -> todayDate, 4, -2);

        echo json_encode(Expense::getCategorySummary($categoryName, $month, $this -> user -> id), JSON_UNESCAPED_UNICODE);
    }
}

    