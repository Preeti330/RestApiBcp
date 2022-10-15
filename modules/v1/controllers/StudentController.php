<?php

namespace app\modules\v1\controllers;
use app\filters\auth\HttpBearerAuth;

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

use app\models\Student;



class StudentController extends ActiveController{

    public $enablecsrfvalidation = false;

    public $modelClass = 'app\models\Student';


    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }


    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            // 'class' => HttpBasicAuth::className(),
            // 'authMethods' => [
            //     HttpBearerAuth::className(),
            // ],



        ];


        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                // 'allow' => true,
                'read' => ['get'],
                'select'=> ['post'],
                'selectbyid'=>['get'],
                'selectbyname'=>['get'],
                'insertintotable'=>['post'],
                'updaterecord'=>['post'],
                'deletebyid'=>['get'],
            ],
        ];


        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];


        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        // $behaviors['authenticator']['except'] = ['options', 'login'];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['read','view','selectbyid','selectbyname','insertintotable','updaterecord','deletebyid'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['read','select','selectbyid','selectbyname','insertintotable','updaterecord','deletebyid'],
                    'roles' => ['?'],
                ],
            ],
        ];
//   print_r( $behaviors);exit;
        return $behaviors;

    }


    public function actions()
    {

        return [];

    }


    public function actionRead(){
       print_r(Yii::$app->request->get());
       $objStu=new Student();
       $obj=$objStu->find()->all();
    //    echo "<pre>";
    //    print_r($obj);
    //    echo "</pre>";
       foreach($obj as $dataval){
        echo $dataval->sid." ".$dataval->name." ".$dataval->city."<br>";
    }
    }


    public function actionSelect()
{
    //    $id=Yii::$app->request->post('name':id);
       print_r($id);

}

public function actionSelectbyid(){

    $id=Yii::$app->request->get(name:'id');

    $objStu= new Student();
    $data=$objStu->find()
          ->where(['sid'=>$id])
          ->one();
          if($data){
            return $data;
          }else{
            return "Id Not Found / SQL Error ";
          }
}

public function actionSelectbyname(){
    $name=Yii::$app->request->get(name:'name');
    // echo "Select By Name : $name<br>";
    $objStu=new Student();
  $data=$objStu->find()
           ->filterwhere(['like','name',$name])
           ->all();
        //    if($data){
        //     foreach($data as $dataval){
        //         echo $dataval->sid." ".$dataval->name." ".$dataval->city."<br>";
        //     }
        //   }else{
        //     echo "error";
        //   }
        if($data){
            return $data;
        }else{
            return " Name Not Found ?SQL Error ";
        }

}

public function actionInsertintotable(){
    $name=Yii::$app->request->post();
    // $name1=json_encode($name);
    // print_r($name1);
    // print_r($this->asJson(\Yii::$app->request->post()));

    $json=file_get_contents('php://input');
    // print_r($json);
    $jsonObj=json_decode($json);
    $name= $jsonObj->name;
    $sid=$jsonObj->sid;
    $city=$jsonObj->city;
    // echo $name." ".$sid." ".$city."<br>";


   $objStu= new Student();
   $objStu->sid=$sid;
   $objStu->name=$name;
   $objStu->city=$city;
  $objStu->save();


   // raw query to insert
/*
   $conn=Yii::$app->db;
   $sql="insert into students(name,city)values('$name','$city')";
  $objStu= $conn->createCommand($sql)->execute();
   if($objStu){
    echo "Data Saved ";
    return $objStu;
   }else{
    return "Data Not Saved / SQL ERROR ";
   }
   */

}

public function actionUpdaterecord(){

    $json=file_get_contents('php://input');
    $jsonObj=json_decode($json);
    $sid=$jsonObj->sid;
    $name=$jsonObj->name;
    $city=$jsonObj->city;
    // echo $name." ".$sid." ".$city."<br>";
    $objStu =new Student();
    $obj=$objStu->find()
    ->where(['sid'=>$sid])
    ->one();
    $obj->name=$name;
    $obj->city=$city;
    if($obj){
        // echo $obj->sid." ".$obj->name." ".$obj->city."<br> ";
        $obj->save();
        return $obj;
    }else{
        return "ID Not Found / SQL Error";
    }

}


public function actionDeletebyid(){
  $sid=Yii::$app->request->get(name:'id');
//  echo $sid;
  $objStu=new Student();
  $data=$objStu->find()
         ->where(['sid'=>$sid])
         ->one();

 if($data){
   $data->delete();
    echo " Record Deleted<br>";
    return $data;
 }else{
    return "Id Not Found....!!!";
 }

}

}
?>
