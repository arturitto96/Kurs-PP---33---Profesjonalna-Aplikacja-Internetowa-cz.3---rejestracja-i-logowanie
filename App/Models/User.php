<?php

namespace App\Models;

use PDO;
use \App\Token;
use \App\Mail;
use \Core\View;
use \App\Models\Income;
use \App\Models\Expense;

/**
 * User model
 *
 * PHP version 7.0
 */
class User extends \Core\Model
{
    /**
     * User id
     *
     * @var integer
     */
    public $id;
    /**
     * User name
     *
     * @var string
     */
    public $username;
    /**
     * User email address
     *
     * @var string
     */
    public $email;
    /**
     * User password
     *
     * @var string
     */
    public $password;
    /**
     * User password after hashing
     *
     * @var string
     */
    public $password_hash;

    public $remember_token;

    public $expiry_timestamp;

    public $password_reset_hash;

    public $password_reset_expiry;

    public $password_reset_expires_at;

    public $activation_token;

    public $activation_hash;

    public $is_active;

    public $app_mode;

    public $password_reset_token;

    /**
     * Error messages
     *
     * @var array
     */
    public $errors = [];

    /**
     * Incomes categories assigned to user
     *
     * @var array
     */
    public $incomesCategories = [];

    /**
     * Expenses categories assigned to user
     *
     * @var array
     */
    public $expensesCategories = [];
    
    /**
     * Payment methods assigned to user
     *
     * @var array
     */
    public $paymentMethods = [];

    public $incomeSummary;

    public $expenseSummary;

    public $incomeFullSummary = [];

    public $expenseFullSummary = [];


    /**
     * Class constructor
     *
     * @param array $data  Initial property values (optional)
     *
     * @return void
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    /**
     * Save the user model with the current property values
     *
     * @return boolean  True if the user was saved, false otherwise
     */
    public function save()
    {
        $this->validate();

        if (empty($this->errors)) {

            $this->password_hash = password_hash($this->password, PASSWORD_DEFAULT);
            
            $token = new Token();
            $hashed_token = $token -> getHash();
            $this -> activation_token = $token -> getValue();

            $sql = 'INSERT INTO users (username, email, password_hash, activation_hash)
                    VALUES (:username, :email, :password_hash, :activation_hash)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $this->password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validate($update = false)
    {
        // Name
        if ($this->username == '') {
            $this->errors[] = 'Imię jest wymagane';
        }

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Nieprawidłowy adres email';
        }
        if (static::emailExists($this->email, $this->id ?? NULL)) {
            $this->errors[] = 'Taki adres email istnieje już w bazie';
        }

        // Password
        if ($update == false || $this -> password != '') {
            if (strlen($this->password) < 6) {
                $this->errors[] = 'Hasło musi zawierać przynajmniej 6 znaków';
            }
    
            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
                $this->errors[] = 'Hasło musi posiadać przynajmniej jedną literę';
            }
    
            if (preg_match('/.*\d+.*/i', $this->password) == 0) {
                $this->errors[] = 'Hasło musi posiadać przynajmniej jedną cyfrę';
            }
        }
    }

    /**
     * See if a user record already exists with the specified email
     *
     * @param string $email email address to search for
     *
     * @return boolean  True if a record already exists with the specified email, false otherwise
     */
    public static function emailExists($email, $ignore_id = NULL)
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user -> id != $ignore_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Find a user model by email address
     *
     * @param string $email email address to search for
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Authenticate a user by email and password.
     *
     * @param string $email email address
     * @param string $password password
     *
     * @return mixed  The user object or false if authentication fails
     */
    public static function authenticate($email, $password)
    {
        $user = static::findByEmail($email);

        if ($user && $user->is_active) {
            if (password_verify($password, $user->password_hash)) {
                return $user;
            }
        }

        return false;
    }

    /**
     * Find a user model by ID
     *
     * @param string $id The user ID
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Remember the login by inserting a new unique token into the remembered_logins table
     * for this user record
     *
     * @return boolean  True if the login was remembered successfully, false otherwise
     */
    public function rememberLogin()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();

        //$expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now
        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30;  // 30 days from now

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Send password reset instructions to the user specified
     * 
     * @param string $email The email address
     * 
     * @return void
     */
    public static function sendPasswordReset($email) {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->startPasswordReset()) {
                $user -> sendPasswordResetEmail();
            }
        }
    }

    /**
     * Start the password reset process by generating a new token and expiry
     * 
     * @return void
     */
    public function startPasswordReset() {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this -> password_reset_token = $token -> getValue();

        $expiry_timestamp = time() + 60 * 60 * 2; //2 hours from now

        $sql = 'UPDATE users
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Send password reset instructions in an email to the user
     *
     * @return void
     */
    protected function sendPasswordResetEmail() {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);

        Mail::send($this->email, 'Resetowanie hasła', $text, $html);
    }

    /**
     * Find a user by password reset token and expiry
     * 
     * @param string $token Password reset token sent to user
     *
     * @return mixed User object if found and token hasn't expired, NULL otherwise
     */
    public static function findPasswordReset($token) {
        $token = new Token($token);
        $hashed_token = $token->getHash();

        $sql = 'SELECT * FROM users
                WHERE password_reset_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        $user = $stmt->fetch();

        if($user) {
            //Check password reset token hasn't expired
            if (strtotime($user -> password_reset_expires_at) > time()) {
                return $user;
            }
        }
    }

    /**
     * Reset the password
     * 
     * @param string $password The new password
     *
     * @return boolean True if the password was updated successfully, false otherwise
     */
    public function resetPassword($password) {
        $this -> password = $password;

        $this -> validate();

        if (empty($this -> errors)) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET password_hash = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_expires_at = NULL
                    WHERE id = :id';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            return $stmt->execute();
        }
        return false;
    }

    /**
     * Send an email to the user containing the activation link
     *
     * @return void
     */
    public function sendActivationEmail() {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        Mail::send($this->email, 'Aktywacja konta w serwisie smartBudget', $text, $html);
    }

    /**
     * Activate the user account with the specified activation token
     * 
     * @param string $value Activation token from the URL
     *
     * @return void
     */
    public static function activate($value) {
        $token = new Token($value);
        $hashed_token = $token -> getHash();

        static::copyDefault($hashed_token);

        $sql = 'UPDATE users
                SET is_active = 1,
                    activation_hash = null
                WHERE activation_hash = :hashed_token';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Update the user profile
     * 
     * @param array $data Data from the edit profile form
     *
     * @return boolean True if the data was updated, false otherwise
     */
    public function updateProfile($data) {
        $this -> username = $data['newUserName'];
        $this -> email = $data['newEmail'];

        if ($data['newPassword'] != '') {
            $this -> password = $data['newPassword'];
        }

        $this -> validate(true);

        if(empty($this -> errors)) {
            $sql = 'UPDATE users
                     SET username = :username,
                         email = :email';

            if ($this->password != '') {
                $sql .= ', password_hash = :password_hash';
            }
                        
            $sql .= "\nWHERE id = :id";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);

            if ($this -> password != '') {
                $this -> password_hash = password_hash($this -> password, PASSWORD_DEFAULT);
                $stmt->bindValue(':password_hash', $this -> password_hash, PDO::PARAM_STR);
            }

            return $stmt -> execute();
        }
        return false;
    }

    /**
     * Delete the user profile
     * 
     *
     * @return void
     */
    public function deleteProfile() {
        $sql = 'DELETE FROM users 
                WHERE users.id = :user_id;
                DELETE FROM remembered_logins
                WHERE remembered_logins.user_id = :user_id;
                DELETE FROM incomes
                WHERE incomes.user_id = :user_id;
                DELETE FROM incomes_category_assigned_to_users 
                WHERE incomes_category_assigned_to_users.user_id = :user_id;
                DELETE FROM expenses
                WHERE expenses.user_id = :user_id;
                DELETE FROM expenses_category_assigned_to_users
                WHERE expenses_category_assigned_to_users.user_id = :user_id;
                DELETE FROM payment_methods_assigned_to_users
                WHERE payment_methods_assigned_to_users.user_id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $this -> id, PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     * Get user from data base by activation token
     *
     * @return mixed
     */
    protected static function getUserByActivationToken($hashed_token) {
        $sql = 'SELECT * FROM users
                WHERE activation_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Copy the default categories for new user
     *
     * @return void
     */
    protected static function copyDefault($hashed_token) {
        $user = static::getUserByActivationToken($hashed_token);
        Income::assignCategoriesToUser($user -> id);
        Expense::assignCategoriesToUser($user -> id);
        Expense::assignPaymentToUser($user -> id);
    }

    /**
     * Get the categories
     * 
     * @return void
     */
    public function getUserCategories() {
        $this -> incomesCategories = Income::getUserCategories($this -> id);
        $this -> expensesCategories = Expense::getUserCategories($this -> id);
        $this -> paymentMethods = Expense::getUserPayment($this -> id);
    }

    /**
     * Get income and expense summary
     *
     * @return void
     */
    public function getShortSummary($startDate, $endDate) {
        $this -> incomeSummary = Income::getShortSummary($this -> id, $startDate, $endDate);
        $this -> expenseSummary = Expense::getShortSummary($this -> id, $startDate, $endDate);
    }

    /**
     * Get income and expense summary
     *
     * @return void
     */
    public function getFullSummary($startDate, $endDate) {
        $this -> incomeFullSummary = Income::getFullSummary($this -> id, $startDate, $endDate);
        $this -> expenseFullSummary = Expense::getFullSummary($this -> id, $startDate, $endDate);

        $this -> expenseFullSummary = json_encode($this -> expenseFullSummary, JSON_NUMERIC_CHECK);
        $this -> expenseFullSummary = json_decode($this -> expenseFullSummary, true);
    }

    /**
     * Updates picked app mode
     *
     * @return boolean True if the data was updated, false otherwise
     */
    public function updateUserMode() {
        $sql = 'UPDATE users
                SET app_mode = :app_mode
                WHERE users.id = :user_id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':app_mode', $this -> app_mode, PDO::PARAM_BOOL);
        $stmt->bindValue(':user_id', $this -> id, PDO::PARAM_INT);

        return $stmt->execute();
    }

}