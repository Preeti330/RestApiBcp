<?php

namespace app\modules\v1\controllers;
use app\filters\auth\HttpBearerAuth;


use app\models\LoginForm;
use app\models\User;
use app\models\UserSearch;

use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\helpers\Url;
use yii\rbac\Permission;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;


use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\QueryParamAuth;

use app\models\Student;
use app\models\Transaction;
use app\models\Bookcopy;
use app\models\Userdetail;
use app\models\Category;



class TransactionController extends ActiveController{


    public $enablecsrfvalidation = false;

    public $modelClass3 = 'app\models\Student';
    public $modelClass2 = 'app\models\Transaction';
    public $modelClass1 = 'app\models\Bookcopy';
    public $modelClass = 'app\models\Userdetail';
    public $modelClass4 = 'app\models\Category';
    public $modelClass5 = 'app\models\User';



    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actions()
    {
        $actions = parent::actions();
    }

    public function behaviors(){
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),

        ];


        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                "displayname"=>['post'],
                "readdata"=>['post'],
                "userinfo"=>['post'],
            ],
        ];

        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];


        $behaviors['authenticator'] = $auth;

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['displayname','readdata','userinfo'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['displayname','readdata','userinfo'],
                    'roles' => ['?'],
                ],
            ],
        ];
//   print_r( $behaviors);exit;
        return $behaviors;

    }



    public function actionDisplayname(){

        $objStu= new Student();
        $objStu->sid=22;
        $objStu->name='preeti';
        // $objStu->city='hyd';
        // print_r($objStu);exit;
    // //    $objStu->save();

    //  $objTran=new Transaction();

    //  $objTran=new Bookcopy();
    // $objTran->id=1;
    // $objTran->book_id=22;
    // // // print_r($objTrans);exit;
    // $objTran->no_of_copies=36;

    // $objTran->cat_id=3;
    // $objTrans->book_recived='2022/07/11 5:45:40';
    // $objTrans->book_return='2022/07/11 6:6:24';
    // $objTrans->amount_paid=336;
    // $objTrans->amount_pending=33;
    // $objTrans->feedback='good';
    // $objTrans->status=1;

    // $objTrans->save();
    // print_r($objTran);
    return $objStu;

    }


    public function actionReaddata(){
      $obj=  new Bookcopy();

    //   $obj->id=4;
    //   $obj->book_id=12;
    //   $obj->no_of_copies=12;

    //   $obj->updated_date=date('Y-m-d H:i:s');
    //   print_r($obj);exit;
    // //   print_r($obj);
    //   return $obj;


    $obj= new User();
    $obj->username='ccc';
    print_r($obj);exit;
    return $obj;

    /*
    $conn=Yii::$app->db;
    $sql="insert into bookcopies(id,book_id,no_of_copies)values(7,2,43)";
   $objStu= $conn->createCommand($sql)->execute();
    if($objStu){
     echo "Data Saved ";
     return $objStu;
    }else{
     return "Data Not Saved / SQL ERROR ";
    }

    */


    $data=$obj->find()
    ->where(['id'=>7])
    ->one();
    if($data){
      return $data;
    }else{
      return "Id Not Found / SQL Error ";
    }

    }

    public function actionUserinfo(){
echo "hello";
        // $objUser=new Userdetail();
    // //   $obj=  $objUser->find()->all();
    //     $objUser->user_name='pt';
    //     $objUser->user_email='pt';
    //     $objUser->password='pt';
    //     $objUser->roleau=2;
    //      $objUser->accesstoken='pt';
    //      $objUser->access_token_expiry_date='2022-3-4 4:4:54.54';
    //      $objUser->status=1;
    //      $objUser->created_date='2022-3-4 4:4:54.54';
    //      $objUser->updated_date='2022-3-4 4:4:54.54';

    //     $conn=Yii::$app->db;
    //     // $sql="insert into userdetails('user_name','user_email','role_au ','pho_no')values('pp','P@gmal.com',1,2323333)";
    //     $sql="insert into userdeatils values(3,'vvpp','P@gmal.com','igt',1,'ererercsdxca','2022-3-4 4:4:54.54',1,'2022-3-4 4:4:54.54','2022-3-4 4:4:54.54',87868656)";
    //    $obj= $conn->createCommand($sql)->execute();




$usrmodel = new User();

$access_token = $usrmodel->generateAccessToken();
// print_r($access_token);

        return $access_token;

    }


    }




?>
