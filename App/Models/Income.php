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
}