<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\SupplementaryMethods;
use \App\Models\ExpensePaymentMethods;

/**
 * Profile controller
 * 
 * PHP version 7.0
 */
class ExpensePaymentMethodsController extends Profile {

    private $user;

    private $todayDate;

    private $firstDate;
        
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
     * Show the form for editing the user payment methods
     * 
     * @return void
     */
    public function editPaymentMethodsAction() {
        View::renderTemplate('Profile/editPayment.html', ['user' => $this -> user, 'mode' => $this -> getUserMode()]);
    }

    /**
     * Save new name for selected payment method
     * 
     * @return void
     */
    public function saveNewPaymentNameAction() {
        if (ExpensePaymentMethods::editPaymentName($_POST, $this -> user -> id)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/ExpensePaymentMethodsController/editPaymentMethods');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/ExpensePaymentMethodsController/editPaymentMethods');
        }
    }

    /**
     * Delete selected payment method
     * 
     * @return void
     */
    public function deletePaymentAction() {
        if (ExpensePaymentMethods::deletePayment($_POST, $this -> user -> id)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/ExpensePaymentMethodsController/editPaymentMethods');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/ExpensePaymentMethodsController/editPaymentMethods');
        }
    }

    /**
     * Save new payment method
     * 
     * @return void
     */
    public function saveNewPaymentAction() {
        if (ExpensePaymentMethods::saveNewPayment($_POST, $this -> user -> id)) {
            Flash::addMessage('Sukces');
            $this -> redirect('/ExpensePaymentMethodsController/editPaymentMethods');
        } else {
            Flash::addMessage('Niepowodzenie', FLASH::WARNING);
            $this -> redirect('/ExpensePaymentMethodsController/editPaymentMethods');
        }
    }
}