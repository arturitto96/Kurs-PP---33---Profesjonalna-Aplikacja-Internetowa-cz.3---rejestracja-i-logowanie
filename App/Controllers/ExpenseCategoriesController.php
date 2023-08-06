<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\ExpenseCategories;

/**
 * Expense controller
 * 
 * PHP version 7.0
 */
class ExpenseCategoriesController extends Profile {

    /**
     * Before filter - called before each action method
     * 
     * @return void
     */
    public function before() {
        parent::before();

        $this -> user = Auth::getUser();
        $this -> user -> getUserCategories();
    }

    /**
     * Show the form for editing the user expenses categories
     * 
     * @return void
     */
    public function editExpenseCategoriesAction() {
        View::renderTemplate('Profile/editExpenses.html', ['user' => $this -> user, 'mode' => $this -> getUserMode()]);
    }

    /**
     * Save new category
     * 
     * @return void
     */
    public function saveNewCategoryAction() {
        if (ExpenseCategories::saveNewCategory($_POST, $this -> user -> id)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/ExpenseCategoriesController/editExpenseCategories');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/ExpenseCategoriesController/editExpenseCategories');
        }
    }

    /**
     * Update selected category
     * 
     * @return void
     */
    public function updateCategoryAction() {
        if (ExpenseCategories::editCategory($_POST, $this -> user -> id)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/ExpenseCategoriesController/editExpenseCategories');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/ExpenseCategoriesController/editExpenseCategories');
        }
    }

    /**
     * Delete selected category
     * 
     * @return void
     */
    public function deleteCategoryAction() {
        if (ExpenseCategories::deleteCategory($_POST, $this -> user -> id)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/ExpenseCategoriesController/editExpenseCategories');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/ExpenseCategoriesController/editExpenseCategories');
        }
    }
}