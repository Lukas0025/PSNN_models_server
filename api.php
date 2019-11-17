<?php
header ("Content-Type:text/xml");

include "lib/db.php";
include "config.php";

$db = new database($config->db);
    
//access controll
if (!$config->public && $_POST['pass'] <> $config->access_pass)
    die("bad pass");
    
$action = $_POST['a'];

if ($action == "getModel") {
    
    die(
        $db->get_model($_POST["name"])["model"]
    );
    
} else if ($action == "getModelInfo") {
    
    $model = $db->get_model($_POST["name"]);
    
    //do not pass "model" it could by so big
    
    $result = [
        'name' => $model['modelname'],
        'autor' => $model['username'],
        'loss' => $model['loss'],
        'description' => $model['description']
    ];
    
} else if ($action == "uploadModel" && $config->can_upload) {
    
    if (!$db->is_model($_POST['name'])) {
        //add new
        if ($db->check_user($_POST['user'], $_POST['pass'])) {
            
            $db->add_model(
                $db->get_user_id($_POST['user']),
                $_POST['name'],
                $_POST['model'],
                $_POST['desc'],
                $_POST['loss']
            );
            
            $result = ["message" => "model added. now you can get it as {$_POST['user']}/{$_POST['name']}"];
            
        } else {
            $result = ["message" => "login fail"];
        }
        
    } else {
        //update existed
        //todo
        $result = ["message" => "now you cant update existed models"];
    }
    
} else if ($action == "register" && $config->can_register) {
    
    if (!$db->is_user($_POST["name"])) {
        $db->add_user($_POST["name"], $_POST["pass"]);
        $result = ["message" => "user created"];
    } else {
        $result = ["message" => "user exist"];
    }
    
} else if ($action == "findModel" && $config->can_search) {

    $result = $db->find_model($_POST["name"]);
    
    //add id for get
    for ($i = 0; $i < count($result); $i++) {
        $result[$i]['getId'] = $result[$i]["autor"] . "/" .  $result[$i]['modelname'];
    }

}

echo json_encode($result);

?>