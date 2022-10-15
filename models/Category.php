<?php
namespace app\models;

use Yii;

/**

 */

class Category extends \yii\db\ActiveRecord{

  const STATUS_UNAVALIABLE = 0; // once user returns book
  const STATUS_AVALABLE=1;

  public static function tableName()
  {
      return 'categories';
  }

    /** @inheritdoc */
    public function rules(){
        return[
          [['c_name'],'required'],
          [['c_name',],'string'],
        //   [['status','id'],'integer'],
          [['updated_date'],'safe'],
      ];
    }

    public function attributeLabels()
    {
        return [
        //   'id' =>'id',
            'c_name' =>'c_name',
            'c_desc' =>'c_desc',
            'status' =>'status',
            'updated_date' =>'updated_date',
        ];
    }
}
