<?php
    namespace App\Models;

    use PDO;

    /**
     * Expense model
     *
     * PHP version 7.0
     */
    class Expense extends \Core\Model {

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
            $stmt->bindValue(':expense_category_assigned_to_user_id', ExpenseCategories::getCategoryID($expense['userId'], $expense['category']), PDO::PARAM_INT);
            $stmt->bindValue(':payment_method_assigned_to_user_id', ExpensePaymentMethods::getPaymentID($expense['userId'], $expense['payment']), PDO::PARAM_INT);
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

            $stmt->execute();
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
    }
?>