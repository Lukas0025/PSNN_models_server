<?php
class database {

    private $link;

    function __construct($config) {
        $this->link = mysqli_connect($config->host, $config->user, $config->pass, $config->dbname) or die('mysql error');
        mysqli_set_charset($this->link, 'utf8');
    }

    /*
     * Add model to DB
     * @param int $user - id of user
     * @param str $name - name of model
     * @param str $model - base64 code of model
     * @param str $description
     * @param loss - float loss of netwotk
     */
    public function add_model($user, $name, $model, $description, $loss) {
        $sql = $this->link->prepare("INSERT INTO `models` (`user_id`, `name`, `description`, `loss`, `model`) VALUES (?, ?, ?, ?, ?);");
        $sql->bind_param("issds", $user, $name, $description, $loss, $model);
        $sql->execute();
    }
    
    /* TODO
    public function update_model($user, $pass, $model, $description, $loss) {
        $sql = $this->link->prepare("INSERT INTO `models` (`user_id`, `name`, `description`, `loss`, `model`) VALUES (?, ?, ?, ?, ?);");
        $sql->bind_param("issfs", $user, $name, $description, $loss, $model);
        $sql->execute();
    }
    */
    
    /*
     * Get ID of user
     * @param str $name - name of user
     * @return int
     */
    public function get_user_id($name) {
        $sql = $this->link->prepare("SELECT id FROM users WHERE name = ?");
        $sql->bind_param("s", $name);
        $sql->execute();
        
        $result = $sql->get_result()->fetch_array();
        
        return $result['id'];
    }
    
    /*
     * is user exist
     * @param str $name - name of user
     * @return boolean
     */
    public function is_user($name) {
        $sql = $this->link->prepare("SELECT * FROM users WHERE name = ?");
        $sql->bind_param("s", $name);
        $sql->execute();
        
        $result = $sql->get_result();
        
        if ($result->num_rows <> 0) {
            return true;
        }
        
        return false;
    }
    
    /*
     * is model exist
     * @param str $name - name of model
     * @return boolean
     */
    public function is_model($name) {
        $sql = $this->link->prepare("SELECT * FROM models WHERE name = ?");
        $sql->bind_param("s", $name);
        $sql->execute();
        
        $result = $sql->get_result();
        
        if ($result->num_rows <> 0) {
            return true;
        }
        
        return false;
    }
    
    /*
     * add new user
     * @param str $name - name of user
     * @param str $pass - pass for user
     * @return null
     */
    public function add_user($name, $pass) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        
        $sql = $this->link->prepare("INSERT INTO `users` (`name`, `pass_hash`) VALUES (?, ?);");
        $sql->bind_param("ss", $name, $hash);
        $sql->execute();
    }
    
    /*
     * check user auth is ok
     * @param str $name - name of user
     * @param str $pass - pass for user
     * @return boolean
     */
    public function check_user($name, $pass) {
        if (!$this->is_user($name)) {
            return false;
        }
        
        $sql = $this->link->prepare("SELECT pass_hash FROM users WHERE name = ?");
        $sql->bind_param("s", $name);
        $sql->execute();
        
        $result = $sql->get_result()->fetch_array();
        
        if (password_verify($pass, $result['pass_hash'])) {
            return true;
        }
        
        return false;
    }
    
    /*
     * get info about model
     * @param str $name - name of model
     * @return fetch array
     */
    public function get_model($name) {
        $name_parts = explode("/", $name);
        
        $sql = $this->link->prepare("SELECT `models`.`model` as `model`, `models`.`name` as `modelname`, `models`.`loss` as `loss`, `models`.`description` as `description`, `users`.`name` as `username` FROM `models` INNER JOIN users ON `models`.`user_id` = `users`.`id` WHERE `users`.`name` = ? AND `models`.`name` = ?");
        $sql->bind_param("ss", $name_parts[0], $name_parts[1]);
        $sql->execute();
        $result = $sql->get_result();
        return $result->fetch_array();
    }
    
    /*
     * add new user
     * @param str $name - name of model (not complete like for search)
     * @return array of fetchs arrays
     */
    public function find_model($name) {
        $sql = $this->link->prepare("SELECT `users`.`name` as `autor`, `models`.`name` as `modelname`, `models`.`loss`, `models`.`description` as `description` FROM `models` INNER JOIN users ON `models`.`user_id` = `users`.`id` WHERE (`models`.`name` LIKE CONCAT('%',?,'%') OR `models`.`description` LIKE CONCAT('%',?,'%')) ORDER BY `models`.`name` ASC");
        //pad all to %%
        $sql->bind_param("ss", $name, $name);
        $sql->execute();
        $result = $sql->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}