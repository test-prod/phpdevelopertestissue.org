<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\models\Youtube;

require(__DIR__ . '/../../vendor/google/apiclient/src/Google/autoload.php');

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
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
     * @inheritdoc
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
     * @return mixed
     */
    public function actionIndex()
    {
        //video's id's
        $videosArray    = ['98k6DanZA9c','295HVGMDSdE','ViGhU-Oefyc','Nx6qog4O07Y','bWl7xTNv7tI'];
        //this video's channel id
        $channelId      = 'UCeGcncQ8m7b1gIco3b9sosQ';

        //Google SDK
        $client = new \Google_Client();
        $client->setClientId(Yii::$app->params['GClientID']);
        $client->setClientSecret(Yii::$app->params['GClientSecret']);
        $client->setRedirectUri(Yii::$app->params['GRedirectURI']);
        $client->setScopes(['https://www.googleapis.com/auth/youtube']);
        $client->setAccessType('offline');

        $youtube = new \Google_Service_YouTube($client);

        // 1 step auth
        if (Yii::$app->request->get('code')) {
            if (strval(Yii::$app->session['state']) !== strval(Yii::$app->request->get('state'))) {
                Yii::$app->session->setFlash('error', 'The session state did not match.');
            }

            $client->authenticate(Yii::$app->request->get('code'));
            Yii::$app->session['token'] = $client->getAccessToken();
            return $this->redirect('/');
        }

        // 2 step auth
        if (isset(Yii::$app->session['token'])) {
            $client->setAccessToken(Yii::$app->session['token']);
        }
        else{
            $auth = $client->createAuthUrl();
            Yii::trace('my_actionIndex_auth');
            Yii::trace($auth);
            return $this->render('login', ['auth' => $auth]);
        }

        // after auth
        if ($client->getAccessToken()) {
            // get subsriptions, one for all video's
            $subsriptions = $youtube->channels->listChannels("statistics", array(
                'id' => $channelId
            ));

            foreach($videosArray as $va){
                $video = $youtube->videos->listVideos("snippet,statistics", array(
                    'id' => $va
                ));
                // get video info
                $videos[] = Youtube::getVideo($video, $subsriptions);
            }

            return $this->render('index',['videos' => $videos]);
        }

    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
