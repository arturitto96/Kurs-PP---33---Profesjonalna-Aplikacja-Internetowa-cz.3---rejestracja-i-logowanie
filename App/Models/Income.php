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
            $sql = 'INSERT INTO	incomes_category_assigned_to_users(name)
                SELECT name FROM incomes_category_default;
                UPDATE incomes_category_assigned_to_users
                SET user_id = :user_id';

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
}