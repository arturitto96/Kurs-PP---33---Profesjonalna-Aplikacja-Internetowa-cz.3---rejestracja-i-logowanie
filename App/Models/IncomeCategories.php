<?php
    namespace App\Models;

    use PDO;
    use \App\Models\Income;

    /**
     * Model of categories for incomes
     *
     * PHP version 7.0
     */
    class IncomeCategories extends \Core\Model {
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
                Income::updateIncomeCategory($userId, $deleteCategoryId, 0);
            } else {
                Income::updateIncomeCategory($userId, $deleteCategoryId, $newCategoryId);
            }

            return $result;
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
?>