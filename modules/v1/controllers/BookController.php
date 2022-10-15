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
use app\models\User;
use app\models\Userdetail;
use app\models\Book;
use app\models\Bookavailable;
use app\models\Bookcopy;
use app\models\Category;
use app\models\Mapisbn;
use app\models\Transaction;
use LDAP\Result;
use DateTime;

class BookController extends ActiveController{

    public $enablecsrfvalidation = false;

    public $modelClass = 'app\models\Book';


    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        // $model = new Userdetail();

        // $model->roles = [
        //     User::ROLE_USER,
        //     // Userdetail::ROLE_ADMIN,
        // ];
    }

    public function actions()
    {
       $actions = parent::actions();
       return $actions;
    }


    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            // 'class' => HttpBasicAuth::className(),
            // 'authMethods' => [
            //     // HttpBearerAuth::className(),
            //     'class' => 'yii\rbac\PhpManager',
            // ],

        ];


        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                // 'allow' => true,
                'adduser' => ['post'],
                'updateuser' => ['post'],
                'loginpage' => ['post'],
                'selectuser' => ['get'],
                'selectbooklist' => ['get'],
                'updatebooklist' => ['post'],
                // 'deletebooklist' => ['get'],
                'addbooklist' => ['post'],
                'findbookcopies' => ['get'],
                'addbookcopy' => ['post'],
                'updatebookcopy' => ['post'],
                'addcategory' => ['post'],
                'updatecategory' => ['post'],
                'selectcategory' => ['get'],
                'issuerequest' => ['post'],
                'returnbook' => ['post'],
                'disablebooklist' => ['get'],
                'disableuser' => ['get'],
                'disablecategory' => ['get'],
                'viewbooks' => ['get'],
                'regenratepwd' => ['post'],
                'reportcategory' => ['get'],
                'wishlist' => ['post'],
            ],
        ];


        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Access-Control-Allow-Origin' => ['*'],
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD','OPTIONS'],
                 'Access-Control-Request-Headers' => ['*'],
                // 'Access-Control-Allow-Methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                 //'Allow' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                // 'Access-Control-Allow-Credentials' => false,
                // 'Access-Control-Max-Age' => 3600,
            ],
        ];


        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options','loginpage',
        'findbookcopies',
        'selectcategory',
        'adduser',
        'addcategory',
        'disableuser',
        'addbooklist',
        'updateuser',
        'selectbooklist',
        'disablebooklist',
        'updatebooklist',
        'addbookcopy',
        'wishlist',
    ];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['adduser','selectbooklist','deletebooklist','addbooklist','findbookcopies','addbookcopy','addcategory','updatecategory','selectcategory','updatebookcopy','selectuser','issuerequest','returnbook','disablebooklist','disableuser','disablecategory','viewbooks','regenratepwd','reportcategory','updateuser'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['adduser','selectbooklist','updatebooklist','deletebooklist','addbooklist','findbookcopies','addbookcopy','addcategory','updatecategory','selectcategory','updatebookcopy','updateuser','selectuser','issuerequest','returnbook','disablebooklist','disableuser','disablecategory','viewbooks','regenratepwd','reportcategory'],
                    'roles' => ['?'],
                ],
                 [
                     'allow' => true,
                     'actions' => ['loginpage','selectbooklist','selectcategory','updateuser'],
                     'roles' => ['user'],
                 ],
            ],
        ];
//   print_r( $behaviors);exit;
        return $behaviors;
    }

    public function actionOptions($id = null)
    {
        return 'ok';
    }

    public function actionAdduser(){
        $obj=new Userdetail();
        $token= $obj->getBearerToken();
        if(isset($token)){
           $detail= $obj->getUserDetails($token);
           $access_token_expiry_date=$detail['access_token_expiry_date'];
         $role=$detail['role'];
           if($access_token_expiry_date>=date('Y-m-d H:i:s') && $access_token_expiry_date!=null ){
              if($role==1){
                $json=file_get_contents('php://input');
                $jsonobj=json_decode($json);
                $user_name=$jsonobj->user_name;
                // print_r($user_name);exit;
                $user_email=$jsonobj->user_email;
                $password=$jsonobj->password;
                $address=$jsonobj->address;
                $dob=$jsonobj->dob;
                $pho_no=$jsonobj->pho_no;
                // echo $user_name,$user_email,$password,$pho_no,$dob,$address;exit;
                $password_hash=Yii::$app->security->generatePasswordHash($password);
                $obj=new Userdetail();
                // $objuser=$obj->getTableSchema()->getColumnNames();

                $usrmodel = new User();
                 $usrmodel->generateAccessToken();
                $date=date('Y-m-d H:i:s',$usrmodel->access_token_expired_at);
                $obj->user_name=$user_name;
                $obj->user_email=$user_email;
                $obj->password=$password;
                $obj->access_token = $usrmodel->access_token;   // Token
                $obj->access_token_expiry_date= $date;
                // $obj->access_token_expiry_date= $tokens1->exp;
                // $obj->status=$status;
                $obj->created_date=date('Y-m-d H:i:s');
                $obj->pho_no=$pho_no;
                $obj->password_hash=$password_hash;
                $obj->address=$address;
                $obj->dob=$dob;
                $obj->save();
                return "User details stored sucessfully";
              }else{
                return "Invalid Api Access Request";
              }
              exit;

              //fetch details by postma
        }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
           }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }

    public function actionLoginpage(){
        $pho_no=$_POST['pho_no'];
        $password=$_POST['password'];
        if(preg_match("/^[0-9]{10}$/",$pho_no)){
             $objUser=new Userdetail();
             $name="";$pwd="";$pwd_hash="";
            $result1= $objUser->find()
                     ->where(['pho_no'=>$pho_no])
                     ->andWhere(['password'=>$password])
                     ->one();
                     if($result1 || $result1!=""){
                        $pno=$result1['pho_no'];
                        $pwd=$result1['password'];
                        $password_hash=$result1['password_hash'];
                        $role=$result1['roleau'];
                        // echo $password,$password_hash,$pho_no;exit;
                       if($pno==$pho_no && Yii::$app->getSecurity()->validatePassword($pwd, $password_hash)){
                        $usrmodel = new User();
                         $usrmodel->generateAccessToken();
                        $obj=new Userdetail();
                        $date=date('Y-m-d H:i:s',$usrmodel->access_token_expired_at);
                        $token1=$usrmodel->access_token;
                        $sql="update userdeatils set access_token='$token1',access_token_expiry_date='$date',updated_date=now() where pho_no=$pno";
                        $conn=Yii::$app->db;
                        $result= $conn->createCommand($sql)->queryAll();

                        $data1=["Role"=>$role,"Access_Token"=>$token1,"data"=>$date];
                        $userRecords=["msg"=>"user login success","Data"=>$data1];
                         return $userRecords;
                       }else{
                        return "Invalid Pwd /Username";
                       }
                     }else{
                        return "Invalid Number/Pwd";
                     }
        }else{
            return "Enter Valid 10 Digit Number";
        }
    }

    public function actionUpdateuser(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
            $detail=$objUser->getUserDetails($token);
            $access_token_expiry_date=$detail['access_token_expiry_date'];
            $role=$detail['role'];
            if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
                if($role==1){
                    $json=file_get_contents('php://input');
                    $jsonobj=json_decode($json);
                    $id=$jsonobj->id;
                    $user_name=$jsonobj->user_name;
                    $user_email=$jsonobj->user_email;
                    // $roleau=$jsonobj->roleau;
                    // $status=$jsonobj->status;
                    $pho_no=$jsonobj->pho_no;
                    // echo $user_name,$user_email,$pho_no;

                   $result= $objUser->find()->where(["id"=>$id])->one();
                   if($result){
                    $result->user_name=$user_name;
                    $result->user_email=$user_email;
                    $result->pho_no=$pho_no;
                    $result->updated_date=date('Y-m-d H:i:s');
                    $result->save();
                    return "Data Updated Sucessfully";
                   }else{
                    return "Invalid User_id ";
                   }

                }else{
                    return "Invalid API Access Request ";
                }

            }else{
                throw new \yii\web\HttpException(401, 'Unauthorized user access!');
               }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
        }

        public function actionSelectuser(){

            $objUser=new Userdetail();
            $token=$objUser->getBearerToken();
            if(isset($token)){
                $access_token_expiry_date =$objUser->getUserDetails($token);

                if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){

                    $id=Yii::$app->request->get(name:'id');
                    $objUser=new Userdetail();
                    if($id){
                        $result=$objUser->find()->where(['id'=>$id])->andWhere(['status'=>1])->one();
                         if($result || $result!=""){
                            $userRecord['id']=$result['id'];
                            $userRecord['uFser_name']=$result['user_name'];
                            $userRecord['user_email']=$result['user_email'];
                            $userRecord['pho_no']=$result['pho_no'];
                            $userRecord['roleau']=$result['roleau'];
                            $userRecord['status']=$result['status'];
                            $userRecord['created_date']=$result['created_date'];
                            $userRecord['updated_date']=$result['updated_date'];
                            return $userRecord;

                        }else{
                            return "User Not Found";
                        }

                    }else{
                        $result=$objUser->find()->all();
                        return $result;
                    }
                }else{
                    throw new \yii\web\HttpException(401, 'Unauthorized user access!');
                   }
            }else{
                throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
            }
        }

    public function actionSelectbooklist(){
        $obj=new Userdetail();
        $token= $obj->getBearerToken();
        if(isset($token)){
            $detail= $obj->getUserDetails($token);
           $access_token_expiry_date=$detail['access_token_expiry_date'];
           $role=$detail['role'];
           if($access_token_expiry_date>=date('Y-m-d H:i:s') && $access_token_expiry_date!=null ){
            if($role==1 || $role==2){
                $id=Yii::$app->request->get(name:'id');
                $selectBook=[];$no_of_copies=0;$mapisbn=[];
                $objBook=new Book();
                if($id){
                    $resBook=$objBook->find()->where(['id'=>$id])->andwhere(['status'=>1])->all();
                    if($resBook || $resBook!=""){
                        $objCopy=new Bookcopy();
                        $resBookCopy=$objCopy->find()->where(['book_id'=>$id])->one();
                        if($resBookCopy || $resBookCopy!=""){
                            $no_of_copies=$resBookCopy['no_of_copies'];
                           $copy_id= $resBookCopy['id'];
                           $objMapisbn=new Mapisbn();
                           $resMapisbn=$objMapisbn->find()->where(['book_id'=>$id])->andWhere(['copy_id'=>$copy_id])->andWhere(['status'=>1])->all();
                           if(!empty($resMapisbn)){
                            foreach($resMapisbn as $val){
                                $mapisbn['isbn'][]=$val['isbn'];
                            }
                           }
                            $selectBook=["book"=>$resBook,"no_of_copies"=>$no_of_copies,"isbn"=>$mapisbn];
                            return $selectBook;
                        }else{
                            throw new \yii\base\Exception("Book Not Available" );
                        }
                    }else{
                        return "Book Not Available";
                    }
                }else{
                 $result=$objBook->find()->where(['status'=>1])->all();
                 return $result;
                }
            }else{
                return "Invalid API Access Request";
            }
           }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
           }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }


    public function actionUpdatebooklist(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
            $detail=$objUser->getUserDetails($token);
            $access_token_expiry_date=$detail['access_token_expiry_date'];
            $role=$detail['role'];
            if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
                if($role==1){
                    $json=file_get_contents('php://input');
                    $jsonobj=json_decode($json);
                    $id=$jsonobj->id;
                    $b_name=$jsonobj->b_name;
                    $author=$jsonobj->author;
                    $objBook=new Book();
                    $resBook=$objBook->find()
                    ->where(['id'=>$id])
                    ->andWhere(['status'=>1])
                    ->one();
                    if($resBook || $resBook!=""){
                        $resBook->b_name=$b_name;
                        $resBook->author=$author;
                        $resBook->updated_date=date('Y-m-d H:i:s');
                        $resBook->save();
                        return "Book Updated Sucessfully";
                    }else{
                       return "Book Not Found";
                    }
                }else{
                    return "Invalid API Access Request";
                }
            }else{
                throw new \yii\web\HttpException(401, 'Unauthorized user access!');
            }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }

    public function actionDeletebooklist(){
         $objUser=new Userdetail();
         $token=$objUser->getBearerToken();
        if(isset($token)){
            $access_token_expiry_date = $objUser->getUserDetails($token);
            if($access_token_expiry_date>date('Y-m-d H:i:m') && $access_token_expiry_date!=null){
               $id=Yii::$app->request->get(name:'id');
               $objBook=new Book();
                  $data= $objBook->find()
                   ->where(['id'=>$id])
                   ->one();
                   $data->delete();
                   return "id : $id deleted";
            }else{
                throw new \yii\web\HttpException(401, 'Unauthorized user access!');
            }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }

     public function actionAddbooklist(){
       $objUser=new Userdetail();
       $token=$objUser->getBearerToken();
       if(isset($token)){
        $detail=$objUser->getUserDetails($token);
        $access_token_expiry_date=$detail['access_token_expiry_date'];
        $role=$detail['role'];
        if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
           if($role==1){
            $json=file_get_contents('php://input');
            $jsonobj=json_decode($json);
            $b_name=$jsonobj->b_name;
            $author=$jsonobj->author;
            $cat_id=$jsonobj->cat_id;
            $description=$jsonobj->description;
            // $img_cover=$jsonobj->img_cover;
         $objBook=new Book();
         $objCat=new Category();
         $catResult=$objCat->find()->where(['id'=>$cat_id])->andWhere(['status'=>1])->one();
         if($catResult || $catResult!=""){
            // $cat_id=$catResult['id'];
            $result=$objBook->find()->where(['b_name'=>$b_name])->andWhere(['status'=>1])->andWhere(['cat_id'=>$cat_id])->one();
            if($result || $result !=""){
                return "Book Already Exist";
            }else{
                $objBook->b_name=$b_name;
                $objBook->author=$author;
                $objBook->cat_id=$cat_id;
                $objBook->description=$description;
                // $objBook->img_cover=$img_cover;
                $objBook->cat_id=$cat_id;
                $objBook->created_date=date('Y-m-d H:i:s');
                // $objBook->updated_date=date('Y-m-d H:i:s');
                $objBook->save();
                $insert_id = Yii::$app->db->getLastInsertID();
                $objBookCopy=new Bookcopy();
                $objBookCopy->book_id=$insert_id;
                $objBookCopy->save();
                return "Sucessfully Inserted ";
            }
        }else{
            return "Category Not Avaliable";
         }
           }else{
            return "Invalid API Access Request";
           }
       }else{
        throw new \yii\web\HttpException(401, 'Unauthorized user access!');
       }
     }else{
        throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
     }
    }

    //display no of copies

    public function actionFindbookcopies(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
         $detail=$objUser->getUserDetails($token);
         $access_token_expiry_date=$detail['access_token_expiry_date'];
         $role=$detail['role'];
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            if($role==1){
            $isbn=Yii::$app->request->get(name:'isbn');
            $objBook = new Bookcopy();
        if($isbn){
            $conn=Yii::$app->db;
            $sql="select
            b.b_name,c.no_of_copies,m.isbn
           from bookcopies as c
           inner join
           books as b
           on (b.id=c.book_id)
           INNER JOIN mapisbns as m
          ON(m.copy_id=c.id)
          GROUP BY b.b_name,m.isbn,c.no_of_copies,m.status
          having m.isbn='$isbn' and m.status=1";

           $result= $conn->createCommand($sql)->queryOne();
           if($result || $result !=""){
            return $result;
           }else{
              return "$isbn Book  Not Available ";
           }
        }else{
            $conn=Yii::$app->db;
            $sql="select
            b.b_name,c.no_of_copies,m.isbn
           from bookcopies as c
           inner join
           books as b
           on (b.id=c.book_id)
           INNER JOIN mapisbns as m
          ON(m.copy_id=c.id)
          GROUP BY b.b_name,m.isbn,c.no_of_copies,m.status
          having m.status=1
           ";

           $result= $conn->createCommand($sql)->queryAll();
            return $result;
        }

         }else{
               return "Invalid Access To The API";
            }
         }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
         }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }


    public function actionAddbookcopy(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
         $detail=$objUser->getUserDetails($token);
         $access_token_expiry_date=$detail['access_token_expiry_date'];
         $role=$detail['role'];
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            if($role==1){
                $json=file_get_contents('php://input');
                $jsonobj=json_decode($json);
                $isbn=$jsonobj->isbn;
                $book_id=$jsonobj->book_id;
                $no_of_copies=$jsonobj->no_of_copies;
                $objBook=new Book();
                $resBook=$objBook->find()->where(['id'=>$book_id])->andWhere(['status'=>1])->one();
                $len=count($isbn);
                if(($resBook || $resBook!="" )&& $len==$no_of_copies){
                    $objBookCopy=new Bookcopy();
                    $resBookcopy= $objBookCopy->find()->where(['book_id'=>$book_id])->one();
                     if($resBookcopy !="" || $resBookcopy && $len==$no_of_copies){
                         $resBookcopy->no_of_copies=$resBookcopy->no_of_copies+$no_of_copies;
                         $resBookcopy->created_date=date('Y-m-d H:i:s');
                         $copy_id=$resBookcopy['id'];
                         $resBookcopy->save();
                         for($i=0;$i<$len;$i++){
                                $objMapisbn=new Mapisbn();
                             $isbnstr=$isbn[$i];
                            $resMapisbn= $objMapisbn->find()->where(['isbn'=>$isbnstr])->andWhere(['book_id'=>$book_id])->andWhere(['copy_id'=>$copy_id])->one();
                            if($resMapisbn || $resMapisbn!=""){
                                $this->throwException(411,'Duplicate ISBN');
                            }else{
                                $objMapisbn->copy_id=$copy_id;
                                $objMapisbn->book_id=$book_id;
                                $objMapisbn->isbn=$isbnstr;
                                $objMapisbn->save();
                                unset($objMapisbn);
                            }
                         }
                     }else{
                         $objBookCopy->book_id=$bid;
                         $objBookCopy->no_of_copies=$no_of_copies;
                         $objBookCopy->created_date=date('Y-m-d H:i:s');
                         $objBookCopy->save();
                         $copy_id = Yii::$app->db->getLastInsertID();
                         for($i=0;$i<$len;$i++){
                             $objMapisbn=new Mapisbn();
                          $isbnstr=$isbn[$i];
                          $objMapisbn->copy_id=$copy_id;
                          $objMapisbn->book_id=$bid;
                          $objMapisbn->isbn=$isbnstr;
                          $objMapisbn->save();
                          unset($objMapisbn);
                      }
                     }
                     return "Sucessfully Added Book Copy";
                }else{

                    $this->throwException(411,'Invalid Book Copies');
                }
            }else{

                $this->throwException(411,'Invalid API Access Request');
            }
         }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
         }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }


    public function actionUpdatebookcopy(){
       $objUser=new Userdetail();
      $token=$objUser->getBearerToken();
      if(isset($token)){
        $detail=$objUser->getUserDetails($token);
        $access_token_expiry_date=$detail['access_token_expiry_date'];
        $role=$detail['role'];
        if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            if($role==1){
                $isbn=Yii::$app->request->post(name:'isbn');
            $newisbn=Yii::$app->request->post(name:'newisbn');
            $objMapisbn=new Mapisbn();
           $result=$objMapisbn->find()->where(['isbn'=>$isbn])->one();
           if($result !="" && $result){
              $result->isbn=$newisbn;
           $result->save();
           return "Sucessfully Updated";
           }else{
              //$this->throwException(411,'Invalid ISBN');
              return "invalid ISBN";
           }
            }else{
                $this->throwException(411,'Invaild API Access Request');
            }
        }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
         }
      }else{
        throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
    }
    }

    public function actionAddcategory(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
         $detail=$objUser->getUserDetails($token);
         $access_token_expiry_date=$detail['access_token_expiry_date'];
         $role=$detail['role'];
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            if($role==1){
                $json=file_get_contents('php://input');
            $jsonobj=json_decode($json);
            $c_name=$jsonobj->c_name;
            $c_desc=$jsonobj->c_desc;
            $objCat=new Category();
            $result=$objCat->find()->where(['c_name'=>$c_name])->one();
            if($result=="" || !$result){
                $objCat->c_name=$c_name;
                $objCat->c_desc=$c_desc;
                $objCat->save();
               return "Data Saved Sucessfully ";
            }else{
                $this->throwException(411,'Category Already Exist');
           }
            }else{
                $this->throwException(411,'Invalid API Access Request');
            }
         }else{
            $this->throwException(401,'Unauthorized user access!');
         }
        }else{
            $this->throwException(422,'The requested access_token could not be found.');
        }
    }


    public function actionUpdatecategory(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
            $detail=$objUser->getUserDetails($token);
         $access_token_expiry_date=$detail['access_token_expiry_date'];
         $role=$detail['role'];
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            if($role==1){
                $json=file_get_contents('php://input');
                $jsonobj=json_decode($json);
                $id=$jsonobj->id;
                $c_name=$jsonobj->c_name;
                $c_desc=$jsonobj->c_desc;
                $objCat=new Category();
                $result=$objCat->find()->where(['id'=>$id])->andWhere(['status'=>1])->one();
                if($result || $result!=""){
                    $result->c_name=$c_name;
                    $result->c_desc=$c_desc;
                    $result->updated_date=date('Y-m-d H:i:s');
                    $result->save();
                    return "Sucessfully Updated";
                }else{
                    $this->throwException(411,'inavlid API Access Request');
                }
            }else{
                $this->throwException(411,'inavlid API Access Request');
            }
         }else{
            $this->throwException(401,'Unauthorized user access!');
         }
        }else{
            $this->throwException(422,'The requested access_token could not be found.');
        }
    }

    public function actionSelectcategory(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
         $detail=$objUser->getUserDetails($token);
         $access_token_expiry_date=$detail['access_token_expiry_date'];
         $role=$detail['role'];
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            if($role==1){
                $id=Yii::$app->request->get(name:'id');
                $objCat=new Category();
               if($id){
                $result= $objCat->find()->where(['id'=>$id])->andWhere(['status'=>1])->one();
                if($result || $result!=""){
                    $objBook=new Book();
                    $result2=$objBook->find()->where(['cat_id'=>$id])->andwhere(['status'=>1])->all();
                    if($result2 || $result2!=""){
                        return $result2;
                    }else{
                        $this->throwException(411,'Books NOT Exits');
                    }
                }else{
                    $this->throwException(411,'Requested Category Not Exit');
                }
               }else{
                $resultall=$objCat->find()->all();
                return $resultall;
               }
            }else{
                $this->throwException(411,'Invalid API Access');
            }
         }else{
            $this->throwException(401,'Unauthorized user access!');
         }
        }else{
            $this->throwException(422,'The requested access_token could not be found.');

        }
    }

    public function actionIssuerequest(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
         $access_token_expiry_date=$objUser->getUserDetails($token);
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){

            $pho_no=Yii::$app->request->post(name:'pho_no');
            $b_name=Yii::$app->request->post(name:'book_name');
            // echo $pho_no,$book_name;
            $objUser=new Userdetail();
           $result= $objUser->find()->where(['pho_no'=>$pho_no])->andWhere(['status'=>1])->one();
           if($result || $result!=""){
            $user_id=$result['id'];
            $objBook=new Book();
           $result2= $objBook->find()->where(['b_name'=>$b_name])->andWhere(['status'=>1])->one();
           if($result2 || $result2!=""){
            $book_id=$result2['id'];
            $cat_id=$result2['cat_id'];
            // echo $book_id,$cat_id;
            $objBookcopy=new Bookcopy();
            $result3=$objBookcopy->find()->where(['book_id'=>$book_id])->one();
            if($result3 || $result3!=""){
                $copy_id=$result3['id'];
                $no_of_copies=$result3['no_of_copies'];
                // echo $copy_id." ".$no_of_copies." ".$user_id." ".$book_id." ".$cat_id;exit;
                if($no_of_copies>0){
                //   echo "issue book";
                  $objBookAvailable=new Bookavailable();
                  $today=date('Y-m-d H:i:s');

                //   $result4=$objBookAvailable->find()
                //             ->where(['user_id'=>$user_id])
                //            ->andWhere(['book_return'=>null])->all();
                //   print_r($result4);exit;
                $sql="SELECT
                count(book_id),user_id,book_id
               from bookavailables
               GROUP BY user_id,book_return,book_id
             having book_return ISNULL AND COUNT(book_id)<3";

             $conn=Yii::$app->db;
            $result4= $conn->createCommand($sql)->queryAll();

            if($result4!="" || $result4){
                foreach($result4 as $val){
                    $bid=$val['book_id'];
                   if($book_id==$bid){
                    return "User Alredy Have Same Book Copy";
                   }
                   else{
                    $objTransaction=new Transaction();
                    $result5=$objTransaction->find()
                    ->where(['user_id'=>$user_id])->one();
                    $amt_pending=$result5['amount_pending'];
                    $amt_due=$result5['amt_due'];
                    if($amt_due==0){
                        // echo "transaction processed";
                        //save to book_availability
                        $result3->no_of_copies=$result3->no_of_copies-1;
                        $result3->updated_date=date('Y-m-d H:i:m');
                        $result3->save();
                        $objBookAvailable->user_id=$user_id;
                        $objBookAvailable->book_id=$book_id;
                        $objBookAvailable->cat_id=$cat_id;
                        $objBookAvailable->copy_id=$copy_id;
                        $objBookAvailable->book_issued=date('Y-m-d H:i:s');
                        $objBookAvailable->save();
                       $bavl_id= Yii::$app->db->getLastInsertId();
                    //  print_r($bavl_id);exit;
                        //save transaction
                        $objTransaction->user_id=$user_id;
                        $objTransaction->book_id=$book_id;
                        $objTransaction->cat_id=$cat_id;
                        $objTransaction->book_issued=date('Y-m-d H:i:s');
                        $objTransaction->bavl_id=$bavl_id;
                        $objTransaction->save();
                        return "Book Issued ";
                    }else{
                        return "AMOUNT DUE -- $amt_pending/-";
                    }
                   }
                }
            }else{
                return "User Not Eligible To  Issue book ";
            }
            exit;
         }else{
            return "Book Copy Not Available";
         }
            }else{
                return "Book Copy Not Available";
            }
           }else{
            return "Invalid Book";
           }
           }else{
            return "Invalid User";
           }
         }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
         }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }


    public function actionReturnbook(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
         $access_token_expiry_date=$objUser->getUserDetails($token);
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            //fetch details from postman
        $pho_no = Yii::$app->request->post(name:'pho_no');
        $book_name=Yii::$app->request->post(name:'book_name');
        $isbn=Yii::$app->request->post(name:'isbn');
        $objUser=new Userdetail();
        $result=$objUser->find()->where(['pho_no'=>$pho_no])->one();
        if($result || $result!=""){
          $user_id=$result['id'];
          $user_name=$result['user_name'];
          $objBook=new Book();
          $result2=$objBook->find()->where(['b_name'=>$book_name])->one();
          if($result2 || $result2 !=""){
            $book_id=$result2['id'];
            $cat_id=$result2['cat_id'];
            // $objBookcopy=new Bookcopy();
            // $result6=$objBookcopy->find()->where(['book_id'=>$book_id])->one();
            $objMapisbn=new Mapisbn();
            //Find ISBN Is Issued
            $result5=$objMapisbn->find()->where(['isbn'=>$isbn])->andWhere(['book_id'=>$book_id])->one();
            if($result5 || $result5!=""){
                //for the above ISbn,book_id,cat_id check wether transaction happened
                $objTransaction=new Transaction();
            $result3=$objTransaction->find()
            ->where(['book_id'=>$book_id])
            ->andwhere(['cat_id'=>$cat_id])
            ->andWhere(['user_id'=>$user_id])
            ->one();
            //print_r($result3);exit;
            if($result3 || $result3!=""){
                $bavl_id= $result3['bavl_id'];
                $amt_due=$result3['amt_due'];
                $amt_pending=$result3['amount_pending'];
                $book_issued=$result3['book_issued'];
                $book_return=$result3['book_return'];
                print_r($book_return);exit;
                // $no_of_days=date_diff($book_issued,$book_return);
                //
                $date1 = new DateTime($book_issued);
                $date2 = new DateTime($book_return);
                $diff=date_diff($date1,$date2);
                $no_of_days= $diff->format("%a");
                $due=0;
                // print_r($result3);exit;
                if($amt_due==0 || $amt_due ==1){
                    $objBookAvailable=new Bookavailable();
                   $result4= $objBookAvailable->find()->where(['id'=>$bavl_id])->andWhere(['status'=>1])->one();
                   if($result4 || $result4!=""){
                    $objBookcopy=new Bookcopy();
                    $result6=$objBookcopy->find()->where(['book_id'=>$book_id])->one();
                    $result6->no_of_copies=$result6->no_of_copies+1;
                    $result6->updated_date =date('Y-m-d H:i:s');
                    $result6->save();
                     $result5->status=1;
                     $result5->save();
                     $result4->book_return=date('Y-m-d H:i:s');
                     $result4->status=2;
                     $result4->save();
                     if($no_of_days>5){
                        $due=($no_of_days-5)*100;
                        $result3->book_return=date('Y-m-d H:i:s');
                        $result3->status=2;
                        $result3->amt_due=1;
                        $result3->amount_pending=$due;
                        $result3->save();
                    }else{
                        $result3->book_return=date('Y-m-d H:i:s');
                        $result3->status=1;
                        $result3->amt_due=0;
                        $result3->amount_pending=$due;
                        $result3->save();
                    }
                     return "Book Returned Sucessfully <br> Amount - Pending $due/-";
                   }else{
                    // print_r($book_return);
                    return "Invalid Trnsaction Access\Book Returned By $user_name On $book_return and Pending_due is : $amt_pending/-";
                  }
                }else{
                    return "AMOUNT DUE -- $due/-";
               }
            }else{
                return "Invalid Trnsaction Access";
            }
            }else{
                return "Invalid ISBN";
            }
          }else{
            return "Invalid Book";
          }
        }else{
            return "Invalid User";
        }
         }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
         }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }


    public function actionDisablebooklist(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
         $detail=$objUser->getUserDetails($token);
         $access_token_expiry_date=$detail['access_token_expiry_date'];
         $role=$detail['role'];
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            if($role==1){
                $book_name= Yii::$app->request->get(name:'id');
                $objBook=new Book();
                $result=$objBook->find()->where(['id'=>$book_name])->andWhere(['status'=>1])->one();
                if($result || $result!=""){
                 $book_id=$result['id'];
                 $cat_id=$result['cat_id'];
                 $objBookcopy=new Bookcopy();
                 $result2=$objBookcopy->find()->where(['book_id'=>$book_id])->andWhere(['>','no_of_copies',0])->one();
                 if($result2 || $result2!=""){
                     $copy_id=$result2['id'];
                     $no_of_copies= $result2['no_of_copies'];
                     if($no_of_copies !=0 || $no_of_copies>0){
                 $objMapisbn=new Mapisbn();
                  $result3=$objMapisbn->find()->where(['copy_id'=>$copy_id])->andwhere(['book_id'=>$book_id])->andwhere(['status'=>1])->all();
                  if($result3 || $result3!=""){
                   $i=0;
                     foreach($result3 as $val){
                         $val->status=2;
                         $val->save();
                        $i=$i+1;
                     }
                     if($no_of_copies !=0 || $no_of_copies>0){
                         $result2->no_of_copies=$result2->no_of_copies-$i;
                         $result2->updated_date=date('Y-m-d H:i:s');
                         $result2->save();
                         $result->status=2;
                         $result->updated_date=date('Y-m-d H:i:s');
                         $result->save();
                         return "Disabled Book Sucessfully";
                     }
                  }else{
                    $result->status=2;
                    $result->updated_date=date('Y-m-d H:i:s');
                    $result->save();
                     return "Book Disabled";
                  }
                     }else{
                        $result->status=2;
                        $result->updated_date=date('Y-m-d H:i:s');
                        $result->save();
                         return " $book_name Disabled";
                     }
                 }else{
                    $result->status=2;
                    $result->updated_date=date('Y-m-d H:i:s');
                    $result->save();
                      return "$book_name Book Disabled";
                 }
                }else{
                 return "Book Not Available";
                }

            }else{
                return "Invalid API Access Request";
            }

         }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
         }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }

    public function actionDisableuser(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
         $detail=$objUser->getUserDetails($token);
         $access_token_expiry_date=$detail['access_token_expiry_date'];
         $role=$detail['role'];
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            if($role==1){
                $id=Yii::$app->request->get(name:'pho_no');
                $objUser=new Userdetail();
                $resUser=$objUser->find()->where(['pho_no'=>$id])->andWhere(['status'=>1])->one();
                if($resUser || $resUser !=""){
                   $user_id=$resUser['id'];
                   $objTransaction=new Transaction();
                  $resTransaction=$objTransaction->find()->where(['user_id'=>$user_id])->one();
                  if($resTransaction || $resTransaction!=""){
                    $amt_pending=$resTransaction['amount_pending'];
                    $amt_due=$resTransaction['amt_due'];
                    if($amt_due!=0 || $amt_pending>0){
                           return "User Have Pending Due of $amt_pending";
                    }else{
                        $resUser->status=2;
                        $resUser->updated_date=date('Y-m-d H:i:s');
                        $resUser->save();
                        return "User Disabled";
                    }
                  }else{
                    $resUser->status=2;
                    $resUser->updated_date=date('Y-m-d H:i:s');
                    $resUser->save();
                    return "User Disabled";
                  }
                }else{
                   return "User Not Available";
                }
            }else{
                return "Invalid API Access Request";
            }
         }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
         }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }

    public function actionDisablecategory(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
         $access_token_expiry_date=$objUser->getUserDetails($token);
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            $id=Yii::$app->request->get(name:'id');
            $objCat=new Category();
            $sql="SELECT c.id AS cat_id,b.id AS book_id,c.id AS bookcopy_id,m.isbn  FROM categories AS C LEFT JOIN books AS b ON(b.cat_id=c.id) LEFT JOIN bookcopies AS bc   ON(bc.book_id=b.id)
            LEFT JOIN mapisbns AS M
            ON(m.copy_id=bc.id)
            WHERE c.id=".$id."and c.status=1";
            $catDetails=Yii::$app->db->createCommand($sql)->queryAll();
            $transactionFlag=0;
            if(!empty($catDetails) || $catDetails !=""){
                foreach ($catDetails as $value) {
                    $cat_id=$value['cat_id'];
                    $book_id=$value['book_id'];
                    $transactionModel=new Transaction();
                    $transactionDetails=$transactionModel->find()->where(['cat_id'=>$cat_id])->andWhere(['book_id'=>$book_id])->andWhere(['IS', 'book_return', null])->all();
                    if(!empty($transactionDetails)){
                         $transactionFlag=1;
                        $responseData="This Category IS Under Transction Process ";
                        break;
                    }

                }
                if($transactionFlag !=1){
                    foreach ($catDetails as $val) {
                        if($val['book_id'] || $val['cat_id']){

                            $sql1="UPDATE categories SET status=2,updated_date=NOW() WHERE id=".$val['cat_id'];

                            if(Yii::$app->db->createCommand($sql1)->execute()){
                                $responseData="Category Disablebed Sucessfully";
                            }else{
                                $responseData ="nope";
                            }
                            print_r($responseData);
                            print_r("hello");exit;
                             exit;


                            if($val['book_id']){
                                $sql2="UPDATE books SET status=2,updated_date=NOW() WHERE cat_id=".$val['cat_id']."and id=".$val['book_id'];
                                $update=Yii::$app->db->createCommand($sql2)->execute();
                            }
                            if($val['bookcopy_id'] || $val['isbn']){
                                $sql4="UPDATE bookcopies  SET updated_date=NOW(),no_of_copies=0  WHERE id=".$val['bookcopy_id']."AND book_id=".$val['book_id'];
                                if($val['isbn']){
                                    $isbn=$val['isbn'];
                                    $sql3="UPDATE mapisbns SET status=2 WHERE status=1 and isbn='$isbn'";
                                    $update=Yii::$app->db->createCommand($sql3)->execute();
                                }
                                $update=Yii::$app->db->createCommand($sql4)->execute();
                            }
                            $update=Yii::$app->db->createCommand($sql1)->execute();
                    }

                    }

                  //  return $responseData;
                }
               // return $responseData;
            }else{
                $this->throwException(411,'Invalid Category Id');
            }
          // return $catDetails;
            /*
            $result=$objCat->find()->where(['id'=>$id])->andWhere(['status'=>1])->one();
            if($result || $result!=""){
                $cat_id=$result['id'];
                $objBook =new Book();
                $result2=$objBook->find()->where(['cat_id'=>$cat_id])->andWhere(['status'=>1])->all();
                if(!empty($result2)){
                   $book_id=[];
                 foreach($result2 as $val){
                    $book_id[]=$val['id'];
                    $val->updated_date=date('Y-m-d H:i:s');
                    $val->status=2;
                    $val->save();
                 }
                  $objBookcopy=new Bookcopy();
                  $result3=$objBookcopy->find()->where(['book_id'=>$book_id])->andWhere(['>','no_of_copies',0])->all();
                  if(!empty($result3)){
                    $no_of_copies=[];
                    $copies_id=[];
                    foreach($result3 as $val){
                        // $no_of_copies[]=$val['no_of_copies'];
                        $copies_id[]=$val['id'];
                     }
                  $objMapisbn=new Mapisbn();
                  $result4=$objMapisbn->find()->where(['copy_id'=>$copies_id])->andWhere(['book_id'=>$book_id])->andWhere(['status'=>1])->all();
                      $i=0;
                     if(!empty($result4)){
                        foreach($result4 as $val){
                            $val->status=2;
                            $val->save();
                            $i=$i+1;
                        }
                        foreach($result3 as $val){
                            if(!empty($val['no_of_copies'])){
                                $val->updated_date=date('Y-m-d H:i:s');
                             $val->no_of_copies=$val->no_of_copies-$i;
                             $val->save();
                            }
                        }
                        $result->updated_date=date('Y-m-d H:i:s');
                     $result->status=2;
                     $result->save();
                        return "Disabled Category With All Book Copies";
                     }else{
                        $result->updated_date=date('Y-m-d H:i:s');
                        $result->status=2;
                        $result->save();
                        return "Disabled Category With Books(This Books Dont Have Copies To Disable)";
                     }
                     $result->updated_date=date('Y-m-d H:i:s');
                     $result->status=2;
                     $result->save();
                     return "Disabled Category With All Book Copies..";
                  }else{
                    $result->updated_date=date('Y-m-d H:i:s');
                    $result->status=2;
                    $result->save();
                    return "Disabled Category(No Books With This Cat)";
                  }
                  $result->updated_date=date('Y-m-d H:i:s');
                  $result->status=2;
                  $result->save();
                  return "Disabled Category With All Books";
                }else{
                    $result->updated_date=date('Y-m-d H:i:s');
                    $result->status=2;
                    $result->save();
                    return "Disabled Category(No Books With This Cat)";
                }
                $result->updated_date=date('Y-m-d H:i:s');
                $result->status=2;
                $result->save();
                return "Disabled Disabled Category";
            }else{
                return "Category Not Found";
            }
            */
         }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
         }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
    }

    public function actionWishlist(){
        echo "hello";
    }




     public function actionViewbooks(){
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
         $access_token_expiry_date=$objUser->getUserDetails($token);
         if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
            $book_name=Yii::$app->request->get(name:'book_name');
       $objBook=new Book();
       if($book_name){
        $result= $objBook->find()->where(['like','b_name',$book_name])->all();
        return $result;
       }else{
       $result=$objBook->find()->where(['status'=>1])->all();
       return $result;
       }
     }else{
            throw new \yii\web\HttpException(401, 'Unauthorized user access!');
     }
     }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
     }

     public function actionRegenratepwd(){
      $pho_no= Yii::$app->request->post(name:'pho_no');
      $user_email=Yii::$app->request->post(name:'user_email');
    //   $pwd=Yii::$app->request->post(name:'password');
      $newpwd=Yii::$app->request->post(name:'newpassword');
      if(preg_match("/^[0-9]{10}$/",$pho_no)){
        $objUser=new Userdetail();
        $result=$objUser->find()->where(['pho_no'=>$pho_no])->andWhere(['user_email'=>$user_email])->one();
        if($result || $result!=0){
            // print_r($result['pho_no']);exit;
            $u_email=$result['user_email'];
            $num=$result['pho_no'];
          if($num == $pho_no && $u_email==$user_email){
            $usrmodel=new User();
            $usrmodel->generateAccessToken();
            $date=date('Y-m-d H:i:s',$usrmodel->access_token_expired_at);
            $access_token=$usrmodel->access_token;
            $password_hash=Yii::$app->security->generatePasswordHash($newpwd);

            $result->pho_no=$pho_no;
            $result->password=$newpwd;
            $result->access_token=$usrmodel->access_token;
            $result->access_token_expiry_date=$date;
            $result->password_hash=$password_hash;
            $result->updated_date=date('Y-m-d H:i:s');

            // $result->save();
            // var_dump($result->save());exit;
        if($result->save()){
           return "inserted Sucessfully";
        }else{
           print_r($result->errors);
        }
            //  return "Password Updated Sucessfully";
          }else{
            return "invalid Email/pho_no";
          }

        }else{
            return "Inavlid Email/Pho_no";
        }

      }else{
        return "Enter Valid 10 Digit Number";
      }
     }

public function actionReportcategory(){
    $cat_name=Yii::$app->request->get(name:'cat_name');
    $objCat=new Category();
    $result=$objCat->find()->where(['c_name'=>$cat_name])->andWhere(['status'=>1])->one();
    if($result || $result !=""){
        $c_id=$result['id'];
        $objBook=new Book();

        }else{
            return "Requested Category Not Exits";
        }

}

private function throwException($errcode,$errmsg)
{
    throw new \yii\web\HttpException($errcode, $errmsg);
}

}

?>
