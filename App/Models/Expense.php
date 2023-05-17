<?php

namespace App\Models;

use PDO;
use \App\Models\User;

/**
 * Expense model
 *
 * PHP version 7.0
 */
class Expense extends \Core\Model {


    /**
     * Assign the default categories to new user
     *
     * @return void
     */
    public static function assignCategoriesToUser($userId) {
        if($userId) {
            $sql = 'INSERT INTO expenses_category_assigned_to_users(name, user_id)
                    SELECT expenses_category_default.name, users.id FROM expenses_category_default, users
                    WHERE users.id = :user_id';

                    

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

            $stmt -> execute();
        }
    }

    /**
     * Assign the default payment methods to new user
     *
     * @return void
     */
    public static function assignPaymentToUser($userId) {
        if($userId) {
            $sql = 'INSERT INTO payment_methods_assigned_to_users(name, user_id)
                    SELECT payment_methods_default.name, users.id FROM payment_methods_default, users
                    WHERE users.id = :user_id';        

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

            $stmt -> execute();
        }
    }

    /**
     * Get the categories assigned to user
     *
     * @return array
     */
    public static function getUserCategories($userId) {

        $sql = 'SELECT name FROM expenses_category_assigned_to_users
                WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the payment method assigned to user
     *
     * @return array
     */
    public static function getUserPayment($userId) {

        $sql = 'SELECT name FROM payment_methods_assigned_to_users
                WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the ID of the choosen category
     *
     * @return integer ID of category
     */
    protected static function getCategoryID($userId, $categoryName) {

        $sql = 'SELECT id FROM expenses_category_assigned_to_users
                WHERE name = :name AND user_id = :user_id'; 

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':name', $categoryName, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Get the ID of the choosen payment method
     *
     * @return integer ID of payment method
     */
    protected static function getPaymentID($userId, $paymentName) {

        $sql = 'SELECT id FROM payment_methods_assigned_to_users
                WHERE name = :name AND user_id = :user_id'; 

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':name', $paymentName, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchColumn();
    }


    /**
     * Save expense data in database
     *
     * @return void
     */
    public static function saveExpense($expense) {

        $sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment)
                VALUES (:user_id, :expense_category_assigned_to_user_id, :payment_method_assigned_to_user_id, :amount, :date_of_expense, :expense_comment)';
        
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $expense['userId'], PDO::PARAM_STR);
        $stmt->bindValue(':expense_category_assigned_to_user_id', static::getCategoryID($expense['userId'], $expense['category']), PDO::PARAM_INT);
        $stmt->bindValue(':payment_method_assigned_to_user_id', static::getPaymentID($expense['userId'], $expense['payment']), PDO::PARAM_INT);
        $stmt->bindValue(':amount', $expense['amount'], PDO::PARAM_STR);
        $stmt->bindValue(':date_of_expense', $expense['date'], PDO::PARAM_STR);
        if ($expense['comment'] == '') {
            $stmt->bindValue(':expense_comment', 'Brak', PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':expense_comment', $expense['comment'], PDO::PARAM_STR);
        }

        $stmt->execute();
    }

    /**
     * Get summary
     *
     * @return float Summary of expenses
     */
    public static function getShortSummary($userId, $startDate, $endDate) {
        $sql = 'SELECT SUM(expenses.amount) AS Summary
		        FROM expenses
		        WHERE user_id = :user_id AND expenses.date_of_expense >= :start_date AND expenses.date_of_expense <= :end_date'; 

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $endDate, PDO::PARAM_STR);

        $stmt->execute();

        $summary = $stmt->fetchColumn();

        if (!empty($summary)) {
            return $summary;
        } else {
            return 0;
        }
    }

    /**
     * Get expense grouped by categories
     *
     * @return array Summary of expenses
     */
    public static function getFullSummary($userId, $startDate, $endDate) {
        $sql = 'SELECT expenses_category_assigned_to_users.name AS label, SUM(expenses.amount) AS y
		        FROM expenses, expenses_category_assigned_to_users
		        WHERE expenses.expense_category_assigned_to_user_id = expenses_category_assigned_to_users.id AND 
                expenses.user_id = :user_id AND
		        expenses.date_of_expense >= :start_date AND
		        expenses.date_of_expense <= :end_date
		        GROUP BY expenses_category_assigned_to_users.name ORDER BY y DESC';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':end_date', $endDate, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Edit category name
     *
     * @return boolean True when update successfull, false otherwise 
     */
    public static function editCategory($data, $userId) {
        $sql = 'UPDATE expenses_category_assigned_to_users
                SET';

        if($data['newCategoryName'] !== "") {
            $sql .= ' name = :new_category_name';
        } 
        
        if ($data['category_limit'] !== "") {
            if ($data['newCategoryName'] !== "") {
                $sql .= ',';
            }
            $sql .= ' category_limit = :category_limit';
        }
        
        $sql .= ' WHERE name = :old_category_name AND
                user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        if ($data['newCategoryName'] !== "") {
            $stmt->bindValue(':new_category_name', $data['newCategoryName'], PDO::PARAM_STR);
        }

        $stmt->bindValue(':old_category_name', $data['oldCategoryName'], PDO::PARAM_STR);

        if ($data['category_limit'] !== "") {
            $stmt->bindValue(':category_limit', $data['category_limit'] , PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

    /**
     * Edit payment name
     *
     * @return boolean True when update successfull, false otherwise 
     */
    public static function editPaymentName($data, $userId) {
        $sql = 'UPDATE payment_methods_assigned_to_users
                SET name = :new_payment_name
                WHERE name = :old_payment_name AND
                user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':new_payment_name', $data['newPaymentName'], PDO::PARAM_STR);
        $stmt->bindValue(':old_payment_name', $data['oldPaymentName'], PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Delete category
     *
     * @return boolean True when delete successfull, false otherwise 
     */
    public static function deleteCategory($data, $userId) {
        $deleteCategoryId = static::getCategoryID($userId, $data['deleteCategory']);
        $newCategoryId = static::getCategoryID($userId, $data['newCategory']);
        
        $sql = 'DELETE FROM expenses_category_assigned_to_users
                WHERE name = :category_name AND
                user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':category_name', $data['deleteCategory'], PDO::PARAM_STR);

        $result = $stmt -> execute();

        if ($result) {           
            if ($deleteCategoryId == $newCategoryId){
                static::updateExpenseCategory($userId, $deleteCategoryId, 0);
            } else {
                static::updateExpenseCategory($userId, $deleteCategoryId, $newCategoryId);
            }
        }

        return $result;
    }

    /**
     * Update expense category with deleted category
     * 
     * @return void
     */
    public static function updateExpenseCategory($userId, $deletedCategoryId, $newCategoryId) {
        $sql = 'UPDATE expenses
                SET expense_category_assigned_to_user_id = :new_category
                WHERE expenses.expense_category_assigned_to_user_id = :delete_category AND
                expenses.user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':delete_category', $deletedCategoryId, PDO::PARAM_INT);
        $stmt->bindValue(':new_category', $newCategoryId, PDO::PARAM_INT);

        if ($data['category_limit'] === "") {
            $stmt->bindValue(':category_limit', NULL , PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':category_limit', $data['category_limit'] , PDO::PARAM_STR);
        }

        $stmt->execute();
    } 

    /**
     * Delete payment
     * 
     * @return boolean True when delete successfull, false otherwise 
     */
    public static function deletePayment($data, $userId) {
        $deletePaymentId = static::getPaymentID($userId, $data['deletePayment']);
        $newPaymentId = static::getPaymentID($userId, $data['newPayment']);
        
        $sql = 'DELETE FROM payment_methods_assigned_to_users
                WHERE name = :payment_name AND
                user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':payment_name', $data['deletePayment'], PDO::PARAM_STR);

        $result = $stmt->execute();

        if ($result) {           
            if ($deletePaymentId == $newPaymentId){
                static::updateExpensePayment($userId, $deletePaymentId, 0);
            } else {
                static::updateExpensePayment($userId, $deletePaymentId, $newPaymentId);
            }
        }

        return $result;
    }

    /**
     * Update expense payment with deleted category
     * 
     * @return void
     */
    public static function updateExpensePayment($userId, $deletePaymentId, $newPaymentId) {
        $sql = 'UPDATE expenses
                SET payment_method_assigned_to_user_id = :new_payment
                WHERE expenses.payment_method_assigned_to_user_id = :delete_payment AND
                expenses.user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':delete_payment', $deletePaymentId, PDO::PARAM_INT);
        $stmt->bindValue(':new_payment', $newPaymentId, PDO::PARAM_INT);

        $stmt->execute();
    } 

    /**
     * Save new category
     *
     * @return boolean True when save successfull, false otherwise 
     */
    public static function saveNewCategory($data, $userId) {
        $sql = 'INSERT INTO expenses_category_assigned_to_users(name, user_id, category_limit)
                VALUES (:new_category_name, :user_id, :category_limit)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':new_category_name', $data['newCategoryName'], PDO::PARAM_STR);

        if ($data['category_limit'] === "") {
            $stmt->bindValue(':category_limit', NULL , PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':category_limit', $data['category_limit'] , PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

    /**
     * Save new payment method
     *
     * @return boolean True when save successfull, false otherwise 
     */
    public static function saveNewPayment($data, $userId) {
        $sql = 'INSERT INTO payment_methods_assigned_to_users(name, user_id)
                VALUES (:new_payment_name, :user_id)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':new_payment_name', $data['newPaymentName'], PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Get limit value for selected category
     *
     * @return float Limit value, NULL otherwise
     */
    public static function getCategoryLimit($categoryName, $userId) {
        $sql = 'SELECT expenses_category_assigned_to_users.category_limit 
                FROM expenses_category_assigned_to_users 
                WHERE expenses_category_assigned_to_users.user_id = :user_id AND
                expenses_category_assigned_to_users.name = :category_name;';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':category_name', $categoryName, PDO::PARAM_STR);

        $stmt->execute();

        $categoryLimit = $stmt->fetchColumn();

        if (!empty($categoryLimit)) {
            return $categoryLimit;
        } else {
            return 0;
        }   
    }

    /**
     * Get summary for selected category
     *
     * @return float Summary of expenses this month for selected category
     */
    public static function getCategorySummary($categoryName, $month, $userID) {
        $categoryID = static::getCategoryID($userID, $categoryName);
        $month = "%" . $month . "%";

        $sql = 'SELECT SUM(expenses.amount)
                FROM expenses
                WHERE expenses.expense_category_assigned_to_user_id = :category_id AND 
                expenses.user_id = :user_id AND
                expenses.date_of_expense LIKE :month';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':category_id', $categoryID, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userID, PDO::PARAM_INT);
        $stmt->bindValue(':month', $month, PDO::PARAM_STR);

        $stmt->execute();

        $categorySummary = $stmt -> fetchColumn();

        if (!empty($categorySummary)) {
            return $categorySummary;
        } else {
            return 0;
        }   
    }
}