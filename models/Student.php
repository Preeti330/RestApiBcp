<?php
namespace app\models;

use yii\db\ActiveRecord;

use Firebase\JWT\JWT;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\rbac\Permission;
use yii\web\Request as WebRequest;

class Student extends ActiveRecord{
  // private $sid;
  // private $name;
  // private $city;
  public static function tableName()
  {
      return 'students';
  }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
          'sid' => 'sid',
            'name' =>  'name',
            'city' => 'city',

        ];
    }

    public function rule(){
      return[['sid','name'],'required'];
  }

}

?>
