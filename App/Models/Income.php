<?php

    namespace App\Models;

    use PDO;

    /**
     * Income model
     *
     * PHP version 7.0
     */
    class Income extends \Core\Model {
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
            $stmt->bindValue(':income_category_assigned_to_user_id', IncomeCategories::getCategoryID($income['userId'], $income['category']), PDO::PARAM_INT);
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
    }
?>