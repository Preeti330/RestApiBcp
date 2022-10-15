<?php
namespace app\models;

use Yii;

class Transaction extends \yii\db\ActiveRecord{

  public static function tableName()
  {
      return 'transactions';
  }

    /** @inheritdoc */
    public function rules(){
        return[
          [['book_id','user_id'],'required'],
          [['feedback'],'string'],

          [['status','book_id','user_id','cat_id','bavl_id'],'integer'],
          [['updated_date','book_issued','book_return'],'safe'],
      ];
    }

    public function attributeLabels()
    {
        return [
        //   'id' =>'id',
           'user_id' =>'user_id',
            'book_id' =>'book_id',
            'cat_id' =>'cat_id',
            'book_issued' =>'book_issued',
            'book_issued' =>'book_issued',
            'amount_paid' =>'amount_paid',
            'amount_pending' =>'amount_pending',
            'feedback' =>'feedback',
            'status' =>'status',
            // 'updated_date' =>'updated_date',
            'bavl_id' =>'bavl_id',
            'amt_due' =>'amt_due',
        ];
    }
}
