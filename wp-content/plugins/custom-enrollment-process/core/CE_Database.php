<?php


class CE_Database
{
    public const USERS_TABLE_NAME = 'ce_process_users';

    public $usersTableName;

    public function __construct()
    {
        global $wpdb;

        $this->usersTableName = $wpdb->prefix . self::USERS_TABLE_NAME;
    }

    public function install() {
        global $wpdb;

        if($wpdb->get_var("SHOW TABLES LIKE '$this->usersTableName'") != $this->usersTableName) {
            $sql = "CREATE TABLE $this->usersTableName (
             id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
             user_email VARCHAR(50),
             user_name VARCHAR(50),
             last_step MEDIUMINT(4),
             UNIQUE KEY id (id)
          );";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    public function uninstall() {
        global $wpdb;

        if($wpdb->get_var("SHOW TABLES LIKE '$this->usersTableName'") == $this->usersTableName) {
            $wpdb->query("DROP TABLE $this->usersTableName");
        }
    }

    public function addUser($email, $name, $step) {
        global $wpdb;
        $wpdb->insert($this->usersTableName, [
            'user_email' => $email,
            'user_name' => $name,
            'last_step' => $step
        ]);
    }

    public function userExists($email)
    {
        global $wpdb;
        $preparedWhere = $wpdb->prepare("WHERE user_email = %s", $email);
        $userId = $wpdb->get_var("SELECT id FROM $this->usersTableName $preparedWhere");
        return (bool)$userId;
    }

    public function userUpdate($email, $name, $step) {
        global $wpdb;
        if (!$this->userExists($email)) {
            return false;
        }
        return $wpdb->update($this->usersTableName, [
            'user_email' => $email,
            'user_name' => $name,
            'last_step' => $step
        ],
        ['user_email' => $email]);
    }
}