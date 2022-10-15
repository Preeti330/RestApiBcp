<?php
namespace app\models;

use Yii;

/**
 * This is the model class for table "bookcopies".
 *
 * @property integer $id
 * @property integer $book_id
 * @property integer $no_of_copies
 * @property timestamp $updated_date
 */

class Bookcopy extends \yii\db\ActiveRecord{

  const STATUS_UNAVALIABLE = 0; // once admin change the status to disabled
  const STATUS_AVALABLE = 1;//once user register


  public static function tableName()
  {
      return 'bookcopies';
  }

    /** @inheritdoc */
    public function rules(){
        return[
          [['book_id'],'required'],
          [['updated_date'],'safe']
      ];
    }


    public function getBook(){
      return $this->hasone(Book::className(),['id'=>'book_id']);
    }

    public function fields()
    {
        return [
            'b_name',
        ];
    }


    public function attributeLabels()
    {
        return [
          'id' => 'id',
            'book_id' =>  'book_id',
            'no_of_copies' => 'no_of_copies',
            'updated_date' => 'updated_date',
            'created_date' => 'created_date',
            'isbn'=>'isbn',
        ];
    }




}
