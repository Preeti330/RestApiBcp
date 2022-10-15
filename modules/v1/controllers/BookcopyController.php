<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\LoginForm;
use app\models\User;
use app\models\Bookcopy;
use app\models\Userdetail;
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

use Firebase\JWT\JWT;

class BookcopyController extends ActiveController
{
    public $modelClass = 'app\models\Bookcopy';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actions()
    {
        return [];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],

        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'login' => ['post'],
                'getPermissions' => ['get'],
                'loginpage' => ['post'],
                'updateuser' => ['post'],
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
        $behaviors['authenticator']['except'] = ['options', 'login','loginpage'];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete', 'getPermissions',], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'getPermissions','loginpage','updateuser'],
                    'roles' => ['admin','manageStaffs'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * Search staff
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        $search = new UserSearch();
        $search->load(\Yii::$app->request->get());
        $search->in_roles = [User::ROLE_STAFF, User::ROLE_ADMIN];
        $search->not_in_status = [User::STATUS_DELETED];
        if (!$search->validate()) {
            throw new BadRequestHttpException(
                'Invalid parameters: ' . json_encode($search->getErrors())
            );
        }

        return $search->getDataProvider();
    }

    /**
     * Create new staff member from backend dashboard
     *
     * Request: POST /v1/staff/1
     *
     * @return User
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        echo "hello";exit;
        $model = new User();
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$id], true));
        } else {
            // Validation error
            throw new HttpException(422, json_encode($model->errors));
        }

        return $model;
    }

    /**
     * Update staff member information from backend dashboard
     *
     * Request: PUT /v1/staff/1
     *  {
     *    "id": 20,
     *    "username": "testuser",
     *    "email": "test2@test.com",
     *    "unconfirmed_email": "test2@test.com",
     *    "password": "{password}",
     *    "role": 50,
     *    "role_label": "Staff",
     *    "last_login_at": null,
     *    "last_login_ip": null,
     *    "confirmed_at": null,
     *    "blocked_at": null,
     *    "status": 10,
     *    "status_label": "Active",
     *    "created_at": "2017-05-27 17:30:12",
     *    "updated_at": "2017-05-27 17:30:12",
     *    "permissions": [
     *        {
     *            "name": "manageSettings",
     *            "description": "Manage settings",
     *            "checked": false
     *        },
     *        {
     *            "name": "manageStaffs",
     *            "description": "Manage staffs",
     *            "checked": false
     *        },
     *        {
     *            "name": "manageUsers",
     *            "description": "Manage users",
     *            "checked": true
     *        }
     *    ]
     *  }
     *
     *
     * @param $id
     *
     * @return array|null|\yii\db\ActiveRecord
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        echo "hello";exit;


        $model = $this->actionView($id);

        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } else {
            // Validation error
            throw new HttpException(422, json_encode($model->errors));
        }

        return $model;
    }

    /**
     * Return requested staff member information
     *
     * Request: /v1/staff/2
     *
     * Sample Response:
     * {
     *   "success": true,
     *   "status": 200,
     *   "data": {
     *            "id": 2,
     *            "username": "staff",
     *            "email": "staff@staff.com",
     *            "unconfirmed_email": "lygagohur@hotmail.com",
     *            "role": 50,
     *            "role_label": "Staff",
     *            "last_login_at": "2017-05-20 18:58:40",
     *            "last_login_ip": "127.0.0.1",
     *            "confirmed_at": "2017-05-15 09:20:53",
     *            "blocked_at": null,
     *            "status": 10,
     *            "status_label": "Active",
     *            "created_at": "2017-05-15 09:19:02",
     *            "updated_at": "2017-05-21 23:31:32"
     *        }
     *   }
     *
     * @param $id
     *
     * @return array|null|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $staff = User::find()->where(
            [
                'id' => $id
            ]
        )->andWhere(
            [
                '!=',
                'status',
                -1
            ]
        )->andWhere(
            [
                'in',
                'role',
                [User::ROLE_STAFF, User::ROLE_ADMIN]
            ]
        )->one();
        if ($staff) {
            return $staff;
        } else {
            throw new NotFoundHttpException("Object not found: $id");
        }
    }

    /**
     * Delete requested staff member from backend dashboard
     *
     * Request: DELETE /v1/staff/1
     *
     * @param $id
     *
     * @return string
     * @throws ServerErrorHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->actionView($id);

        $model->status = User::STATUS_DELETED;

        if ($model->save(false) === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        $response = \Yii::$app->getResponse();
        $response->setStatusCode(204);
        return 'ok';
    }

    /**
     * Handle the login process for staff members for backend dashboard
     *
     * Request: POST /v1/staff/login
     *
     *
     * @return array
     * @throws HttpException
     */
    public function actionLogin()
    {
        echo "kk";exit;
        $model = new LoginForm();

        $model->roles = [
            User::ROLE_ADMIN,
            User::ROLE_STAFF
        ];
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = $model->getUser();
            $user->generateAccessTokenAfterUpdatingClientInfo(true);

            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
            $id = implode(',', array_values($user->getPrimaryKey(true)));

            $responseData = [
                'id' => $id,
                'access_token' => $user->access_token,
            ];

            return $responseData;
        } else {
            // Validation error
            throw new HttpException(422, json_encode($model->errors));
        }
    }

    /**
     * Return list of available permissions for the staff.  The function will be called when staff form is loaded in backend.
     *
     * Request: GET /v1/staff/get-permissions
     *
     * Sample Response:
     * {
     *        "success": true,
     *        "status": 200,
     *        "data": {
     *            "manageSettings": {
     *                "name": "manageSettings",
     *                "description": "Manage settings",
     *                "checked": false
     *            },
     *            "manageStaffs": {
     *                "name": "manageStaffs",
     *                "description": "Manage staffs",
     *                "checked": false
     *            }
     *        }
     *    }
     */



    public function actionLoginpage(){
        echo "hello";exit;
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
        echo "hello";exit;
        $objUser=new Userdetail();
        $token=$objUser->getBearerToken();
        if(isset($token)){
            $access_token_expiry_date =$objUser->getUserDetails($token);
            if($access_token_expiry_date>date('Y-m-d H:i:s') && $access_token_expiry_date!=null){
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
                throw new \yii\web\HttpException(401, 'Unauthorized user access!');
               }
        }else{
            throw new \yii\web\HttpException(422, 'The requested access_token could not be found.');
        }
        }



    /**
     * Handle OPTIONS
     *
     * @param null $id
     * @return string
     */
    public function actionOptions($id = null)
    {
        return 'ok';
    }
}
