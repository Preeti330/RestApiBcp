<?php

namespace app\models;

use yii\db\ActiveRecord;

use Firebase\JWT\JWT;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\rbac\Permission;
use yii\web\Request as WebRequest;

class Wishlist extends ActiveRecord{

    public $id;
    public $user_id;
    public $book_id;
    public $cat_id;
    public $wish_list;
    public $created_date;


    const STATUS_DEFAULT = 0;
    const STATUS_WISH = 1;

    public static function tableName()
    {
        return 'wishlists';
    }


    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'user_id' => 'user_id',
            'book_id' =>  'book_id',
            'cat_id' =>  'cat_id',
            'wish_list' => 'wish_list',
            'created_date' => 'created_date',
        ];
    }

    public function rule(){
      return[['user_id','book_id','wish_list'],'required'];
  }


}

?>

