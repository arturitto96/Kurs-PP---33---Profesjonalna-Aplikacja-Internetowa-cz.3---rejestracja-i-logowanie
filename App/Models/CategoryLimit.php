<?php

    namespace App\Models;

    use PDO;
    use \App\Models\ExpenseCategories;

    /**
     * Model of limit for expenses categories
     *
     * PHP version 7.0
     */
    class CategoryLimit extends \Core\Model {
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
                return null;
            }   
        }

        /**
         * Get summary for selected category
         *
         * @return float Summary of expenses this month for selected category
         */
        public static function getCategorySummary($categoryName, $month, $userID) {
            $categoryID = ExpenseCategories::getCategoryID($userID, $categoryName);
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

        /**
         * Get limit state for selected category
         *
         * @return boolean Limit state value
         */
        public static function getCategoryLimitState($categoryName, $userId) {
            $sql = 'SELECT expenses_category_assigned_to_users.is_limit_active 
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
         * Get limit state for selected category
         *
         * @return boolean Limit state value, null otherwise
         */
        public static function setLimitState($categoryName, $currentLimitState, $userId) {
            
            $newLimitState = !$currentLimitState;
            
            $sql = 'UPDATE expenses_category_assigned_to_users
                    SET expenses_category_assigned_to_users.is_limit_active = :new_limit_state
                    WHERE expenses_category_assigned_to_users.user_id = :user_id AND
                    expenses_category_assigned_to_users.name = :category_name;';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':new_limit_state', $newLimitState, PDO::PARAM_BOOL);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':category_name', $categoryName, PDO::PARAM_STR);

            return $stmt->execute();

            
        }
    }
?>