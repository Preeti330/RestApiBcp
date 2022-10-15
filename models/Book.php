<?php
namespace app\models;

use Yii;

/**

 */

class Book extends \yii\db\ActiveRecord{

  const STATUS_AVALABLE = 1; // once user returns book
  const STATUS_UNAVALIABLE = 0;

  public static function tableName()
  {
      return 'books';
  }

    /** @inheritdoc */
    public function rules(){
        return[
          [['b_name','cat_id'],'required'],
          [['b_name','author','description','img_cover'],'string'],
          [['cat_id','status'],'integer'],
          [['updated_date','created_date'],'safe'],
      ];
    }

    public function attributeLabels()
    {
        return [
          'id' =>'id',
            'b_name' =>'b_name',
            'author' =>'author',
            'description' =>'description',
            'img_cover' =>'img_cover',
            'cat_id' =>'cat_id',
            'status' =>'status',
            'created_date' =>'created_date',
            'updated_date' =>'updated_date',
        ];
    }
}
