<?php
namespace app\models;

use Yii;

class Bookavailable extends \yii\db\ActiveRecord{

    const STATUS_RETURN = 2; // once user returns book
    const STATUS_ISSUED = 1;//once user issues book

  public static function tableName()
  {
      return 'bookavailables';
  }

    /** @inheritdoc */
    public function rules(){
        return[
          [['book_id','user_id'],'required'],

          [['status','book_id','user_id','cat_id','copy_id'],'integer'],
          [['updated_date','book_issued','book_return'],'safe'],
      ];
    }

    public function attributeLabels()
    {
        return [
        //   'id' =>'id',
            'book_id' =>'book_id',
            'user_id' =>'user_id',
            'book_issued' =>'book_issued',
            'book_return' =>'book_return',
            'cat_id' =>'cat_id',
            'copy_id' =>'copy_id',
            'status' =>'status',
            'updated_date' =>'updated_date',
        ];
    }
}
