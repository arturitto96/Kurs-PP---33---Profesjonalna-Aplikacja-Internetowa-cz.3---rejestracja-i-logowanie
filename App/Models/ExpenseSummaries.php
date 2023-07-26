<?php

    namespace App\Models;

    use PDO;
    use \App\Models\Expense;

    /**
     * Model of summaries for expenses
     *
     * PHP version 7.0
     */
    class ExpenseSummaries extends \Core\Model {
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
    }
?>