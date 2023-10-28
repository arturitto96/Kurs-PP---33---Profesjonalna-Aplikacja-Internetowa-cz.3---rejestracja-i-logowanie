<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\IncomeCategories;

/**
 * Income categories controller
 * 
 * PHP version 7.0
 */
class IncomeCategoriesController extends Profile {

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
     * Show the form for editing the user incomes categories
     * 
     * @return void
     */
    public function editIncomeCategoriesAction() {
        View::renderTemplate('Profile/editIncomes.html', ['user' => $this -> user, 'mode' => $this -> getUserMode()]);
    }

    /**
     * Update selected category
     * 
     * @return void
     */
    public function updateCategoryAction() {
        if (IncomeCategories::editCategoryName($_POST, $this -> user -> id)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/IncomeCategoriesController/editIncomeCategories');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/IncomeCategoriesController/editIncomeCategories');
        }
    }

    /**
     * Delete selected category
     * 
     * @return void
     */
    public function deleteCategoryAction() {
        if (IncomeCategories::deleteCategory($_POST, $this -> user -> id)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/IncomeCategoriesController/editIncomeCategories');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/IncomeCategoriesController/editIncomeCategories');
        }
    }

    /**
     * Save new category
     * 
     * @return void
     */
    public function saveNewCategoryAction() {
        if (IncomeCategories::saveNewCategory($_POST, $this -> user -> id)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/IncomeCategoriesController/editIncomeCategories');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/IncomeCategoriesController/editIncomeCategories');
        }
    }
}