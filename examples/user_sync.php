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
            'groups' => ['a','b','c'],
            'supervisor' => 'Tim Cortesi',
            'pizza' => 'whatever',
            'active'=>0,
        ],
        [
            'unique_id'=>2,
            'first_name'=>'whatever',
            'last_name'=>'Doe',
            'email'=>'jdoe2@example.com',
            'groups' => ['b','c'],
        ],
        [
            'unique_id'=>3,
            'first_name'=>'Tony',
            'last_name'=>'Stark',
            'email'=>'tony.stark@avengers.com',
            'groups' => ['c'],
        ],
        [
            'unique_id'=>4,
            'first_name'=>'I Am',
            'last_name'=>'Groot',
            'email'=>'groot@groot.com',
            'groups' => ['a','d'],
        ],
    ];

    static private $groups = [
        'STUDENTS'=>[1,2],
        'STAFF'=>[2,3,4],
      ];    

    static private $bcomply_url = 'http://localhost:8000';
    static private $bcomply_user = 'defaultuser';
    static private $bcomply_pass = 'defaultpass';
    
    static public function sync() {
        $httphelper = new HTTPHelper();
        $response = $httphelper->http_fetch(
            self::$bcomply_url.'/api/public/sync',
            'POST',
            ['users'=>self::$users,'groups'=>self::$groups],
            self::$bcomply_user,
            self::$bcomply_pass
        );
        var_dump($response['content']);
    }
}

BComplyUserSync::sync();