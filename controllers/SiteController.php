<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

//use Goutte\Client;
//use GuzzleHttp\Client as GuzzleClient;

use app\models\Scrapy;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {   
       
        //echo '<pre />';
        //$urls = array('https://vanilla-paris.co.uk/');
        //$image = 'logo_small_health_1.png';
        //$searchTags = array('Vanilla', 'leather');

        $matchImages = array();
        $matchTags = array();
        $image = '';
        $_filters = array('img' => array('src', 'alt'));
        if (Yii::$app->request->isPost) {

            $data = Yii::$app->request->post();
            $urls = isset($data['urls']) ? $data['urls'] : array();
            $searchTags = isset($data['tags']) ? $data['tags'] : array();
            $file = \yii\web\UploadedFile::getInstanceByName('file');
            if ($file) {
                //$original_name = $file->baseName;  
                $imageName = \Yii::$app->security->generateRandomString().'.'.$file->extension;
                if ($file->saveAs(\Yii::$app->basePath.'/web/uploads/local/'.$imageName)) {
                    $image = $imageName;
                }                            
                
            }

            foreach($urls as $key => $url) {
                $scrapy = new Scrapy();
                $scrapy = $scrapy->setFilters($_filters);
                $scrapy->setUrl($url)->scrapeData();
                $matchImages[$key] = $scrapy->getDataByMatchImage($image);
                $matchTags[$key] = $scrapy->getDataByMatchTags($searchTags);
            }
            
            return $this->render('scraper-result', ['matchImages' => $matchImages, 'matchTags' => $matchTags]);
            
        } else {
            return $this->render('index');
        }
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
