<?php
namespace app\models;

use Yii;

/**

 */

class Mapisbn extends \yii\db\ActiveRecord{

    const STATUS_AVALABLE = 1; // once user returns book//admin add bookcopy
    const STATUS_UNAVALIABLE = 2;//once user issues/adamin disable book copy

  public static function tableName()
  {
      return 'mapisbns';
  }

    /** @inheritdoc */
    public function rules(){
        return[
          [['copy_id','book_id'],'required'],
          [['isbn',],'string'],
      ];
    }

    public function attributeLabels()
    {
        return [
        //   'id' =>'id',
            'copy_id' =>'copy_id',
            'book_id' =>'book_id',
            'isbn' =>'isbn',
        ];
    }
}
