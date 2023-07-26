<?php
    namespace App\Models;

    use PDO;
    use \App\Models\Income;

    /**
     * Model of summaries for incomes
     *
     * PHP version 7.0
     */
    class IncomeSummaries extends \Core\Model {
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
?>