<?php

    namespace App\Models;

    use PDO;
    use \App\Models\Expense;

    /**
     * Model of payment methods for expenses
     *
     * PHP version 7.0
     */
    class ExpensePaymentMethods extends \Core\Model {
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
         * Get the ID of the choosen payment method
         *
         * @return integer ID of payment method
         */
        public static function getPaymentID($userId, $paymentName) {

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
                    Expense::updateExpensePayment($userId, $deletePaymentId, 0);
                } else {
                    Expense::updateExpensePayment($userId, $deletePaymentId, $newPaymentId);
                }
            }

            return $result;
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
    }

?>