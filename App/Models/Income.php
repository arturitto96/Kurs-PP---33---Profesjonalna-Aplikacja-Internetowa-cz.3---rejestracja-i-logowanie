<?php

namespace App\Models;

use PDO;
use \App\Models\User;

/**
 * Income model
 *
 * PHP version 7.0
 */
class Income extends \Core\Model {


    /**
     * Assign the default categories to new user
     *
     * @return void
     */
    public static function assignCategoriesToUser($userId) {
        if($userId) {
            $sql = 'INSERT INTO incomes_category_assigned_to_users(name, user_id)
            SELECT incomes_category_default.name, users.id FROM incomes_category_default, users
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

        $sql = 'SELECT name FROM incomes_category_assigned_to_users
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
    public static function getCategoryID($userId, $categoryName) {

        $sql = 'SELECT id FROM incomes_category_assigned_to_users
                WHERE name = :name AND user_id = :user_id'; 

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':name', $categoryName, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Save income data in database
     *
     * @return void
     */
    public static function saveIncome($income) {

        $sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment)
                VALUES (:user_id, :income_category_assigned_to_user_id, :amount, :date_of_income, :income_comment)';
        
        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $income['userId'], PDO::PARAM_STR);
        $stmt->bindValue(':income_category_assigned_to_user_id', static::getCategoryID($income['userId'], $income['category']), PDO::PARAM_INT);
        $stmt->bindValue(':amount', $income['amount'], PDO::PARAM_STR);
        $stmt->bindValue(':date_of_income', $income['date'], PDO::PARAM_STR);
        if ($income['comment'] == '') {
            $stmt->bindValue(':income_comment', 'Brak', PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':income_comment', $income['comment'], PDO::PARAM_STR);
        }

        $stmt->execute();
    }

    /**
     * Get summary
     *
     * @return float Summary of incomes
     */
    public static function getShortSummary($userId, $startDate, $endDate) {
        $sql = 'SELECT SUM(incomes.amount) AS Summary
		        FROM incomes
		        WHERE user_id = :user_id AND incomes.date_of_income >= :start_date AND incomes.date_of_income <= :end_date';

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
     * Get summary grouped by categories
     *
     * @return array Summary of incomes
     */
    public static function getFullSummary($userId, $startDate, $endDate) {
        $sql = 'SELECT incomes_category_assigned_to_users.name, SUM(incomes.amount) AS Summary
		        FROM incomes, incomes_category_assigned_to_users
		        WHERE incomes.income_category_assigned_to_user_id = incomes_category_assigned_to_users.id AND 
                incomes.user_id = :user_id AND
		        incomes.date_of_income >= :start_date AND
		        incomes.date_of_income <= :end_date
		        GROUP BY incomes_category_assigned_to_users.name ORDER BY Summary DESC';

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
    public static function editCategoryName($data, $userId) {
        $sql = 'UPDATE incomes_category_assigned_to_users
                SET name = :new_category_name
                WHERE name = :old_category_name AND
                user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':new_category_name', $data['newCategoryName'], PDO::PARAM_STR);
        $stmt->bindValue(':old_category_name', $data['oldCategoryName'], PDO::PARAM_STR);

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
        
        $sql = 'DELETE FROM incomes_category_assigned_to_users
                WHERE name = :category_name AND
                user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':category_name', $data['deleteCategory'], PDO::PARAM_STR);

        $result = $stmt -> execute();

        if ($deleteCategoryId == $newCategoryId) {
            static::updateIncomeCategory($userId, $deleteCategoryId, 0);
        } else {
            static::updateIncomeCategory($userId, $deleteCategoryId, $newCategoryId);
        }

        return $result;
    }

     /**
     * Update income with deleted category
     * 
     * @return void
     */
    public static function updateIncomeCategory($userId, $deletedCategoryId, $newCategoryId) {
        $sql = 'UPDATE incomes
                SET income_category_assigned_to_user_id = :new_category
                WHERE incomes.income_category_assigned_to_user_id = :delete_category AND
                incomes.user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':delete_category', $deletedCategoryId, PDO::PARAM_INT);
        $stmt->bindValue(':new_category', $newCategoryId, PDO::PARAM_INT);

        $stmt->execute();
    } 

    /**
     * Save new category
     *
     * @return boolean True when save successfull, false otherwise 
     */
    public static function saveNewCategory($data, $userId) {
        $sql = 'INSERT INTO incomes_category_assigned_to_users(name, user_id)
                VALUES (:new_category_name, :user_id)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':new_category_name', $data['newCategoryName'], PDO::PARAM_STR);

        return $stmt->execute();
    }
}