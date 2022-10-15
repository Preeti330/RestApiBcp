<?php
namespace app\models;

use Yii;

// /**
//  * This is the model class for table "bookcopies".
//  *
//  * @property integer $id
//  * @property integer $book_id
//  * @property integer $no_of_copies
//  * @property timestamp $updated_date
//  */

class Userdetail extends \yii\db\ActiveRecord{

  const ROLE_USER = 2; // role will be user
  const ROLE_ADMIN = 1; // role will be admin

  /**
     * @inheritdoc
     */
  public static function tableName()
  {
      return 'userdeatils';
  }

    /** @inheritdoc */
    public function rules(){
        return[
          [['password','pho_no'],'required'],
          [['user_name','user_email','password','access_token','address','password_hash'],'string'],
          [['pho_no','roleau','status'],'integer'],
          [['updated_date','access_token_expiry_date',],'safe'],
          // [['pho_no'], 'integer', 'max' => 10],
          ['roleau', 'default', 'value' => self::ROLE_USER],
          ['roleau', 'in', 'range' => [self::ROLE_USER, self::ROLE_ADMIN]],
      ];
    }

    public function attributeLabels()
    {
        return [
          // 'id' => 'id',
          'user_name' =>  'user_name',
          'user_email' => 'user_email',
          'password' => 'password',
          'roleau' => 'roleau',
          'access_token' => 'access_token',
          'access_token_expiry_date' => 'access_token_expiry_date',
          'status' => 'status',
          'created_date' => 'created_date',
          'updated_date' => 'updated_date',
          'pho_no' => 'pho_no',
          'address' => 'address',
          'dob' => 'dob',
          'password_hash' => 'password_hash',
        ];
    }


    public function getBearerToken(){
      $headers = Yii::$app->request->headers;
      $accept = $headers->get('authorization');
      //print_r(explode(' ',$accept));
       //print_r($headers['authorization']);exit;
      if($accept){
        $pattern = "/^(?i)Bearer (.*)(?-i)/";
        $token=preg_match($pattern,  $accept,$matches);
        $token=$matches[1];
        return $token;
      }else{
        throw new \yii\web\HttpException(404, 'The requested access_token could not be found.');
      }
}



public function getUserDetails($token){
  $objUser = new Userdetail();
  $result=$objUser->find()->where(['access_token'=>$token])->one();

        if($result || $result!=""){
          $access_token_expiry_date=$result['access_token_expiry_date'];
          $role=$result['roleau'];
         $detail=["access_token_expiry_date"=>$access_token_expiry_date,"role"=>$role];
        
          return $detail;
        }else{
          throw new \yii\web\HttpException(404, 'The requested access_token could not be found.');
        }
}


}
