<?php
chdir(dirname(__file__));

include_once('../app/Libraries/HTTPHelper.php');
use App\Libraries\HTTPHelper;

class BComplyUserSync {
    static private $users = [
        [
            'unique_id'=>1,
            'first_name'=>'John2',
            'last_name'=>'Doe2',
            'email'=>'jdoe1@example.com',
            'supervisor' => 'Tim Cortesi',
            'pizza' => 'whatever',
            'active'=>0,
        ],
        [
            'unique_id'=>2,
            'first_name'=>'whatever',
            'last_name'=>'Doe',
            'email'=>'jdoe2@example.com',
        ],
        [
            'unique_id'=>3,
            'first_name'=>'Tony',
            'last_name'=>'Stark',
            'email'=>'tony.stark@avengers.com',
        ],
        [
            'unique_id'=>4,
            'first_name'=>'I Am',
            'last_name'=>'Groot',
            'email'=>'groot@groot.com',
        ],
    ];

    static private $groups = [
        'STUDENTS'=>[1,2],
        'STAFF'=>[2,3,4],
      ];    

    static private $bcomply_url = 'http://localhost:8000';
    static private $bcomply_user = 'defaultuser';
    static private $bcomply_pass = 'defaultpass';
    
    static private function load_users() {
        $names = json_decode(file_get_contents('names.json'));
        foreach($names as $name) {
            self::$users[] = [
                'unique_id' => 'B00'.$name,
                'first_name' => $name,
                'last_name' => "O'".$name,
                'email' => $name.'@gmail.com'
            ];
        }
    }
    
    static public function sync() {
        self::load_users();
        $httphelper = new HTTPHelper();
        $response = $httphelper->http_fetch([
            'url'=>self::$bcomply_url.'/api/public/sync',
            'verb'=>'POST',
            'data'=>['users'=>self::$users,'groups'=>self::$groups],
            'mime_type'=>'application/json',
            'username'=>self::$bcomply_user,
            'password'=>self::$bcomply_pass
        ]);
        var_dump($response['content']);
    }
}

BComplyUserSync::sync();