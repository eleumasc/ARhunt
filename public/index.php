<?php

require '../vendor/autoload.php';

$app = new \Slim\App(include '../config/settings.php');

include '../config/services.php';

$app->group('/ajax', function() use ($app) {
    $this->group('', function() {
        $this->post('/signin', \ARHunt\Signin\Controller\SigninController::class . ':ajaxSignin')->setName('ajax-signin');
        $this->post('/signup', \ARHunt\Signup\Controller\SignupController::class . ':ajaxSignup')->setName('ajax-signup');
    })->add(new \ARHunt\Signin\Middleware\NoSigninMiddleware($app->getContainer()));

    $this->group('', function() {
        $this->post('/user-exists', \ARHunt\User\Controller\UserController::class . ':ajaxUserExists')->setName('ajax-user-exists');
        $this->post('/hunts/add', \ARHunt\Hunt\Controller\HuntController::class . ':ajaxAdd')->setName('ajax-hunts-add');
        $this->post('/hunts/edit', \ARHunt\Hunt\Controller\HuntController::class . ':ajaxEdit')->setName('ajax-hunts-edit');
        $this->post('/hunts/delete', \ARHunt\Hunt\Controller\HuntController::class . ':ajaxDelete')->setName('ajax-hunts-delete');
        $this->post('/hunts/status', \ARHunt\Hunt\Controller\StatusController::class . ':ajaxStatus')->setName('ajax-hunts-status');
        $this->post('/hunts/verify', \ARHunt\Hunt\Controller\StatusController::class . ':ajaxVerify')->setName('ajax-hunts-verify');
        $this->post('/media/edit', \ARHunt\Media\Controller\MediaController::class . ':ajaxEdit')->setName('ajax-media-edit');
        $this->post('/media/delete', \ARHunt\Media\Controller\MediaController::class . ':ajaxDelete')->setName('ajax-media-delete');
        $this->post('/teams/add', \ARHunt\Team\Controller\TeamController::class . ':ajaxAdd')->setName('ajax-teams-add');
        $this->post('/teams/edit', \ARHunt\Team\Controller\TeamController::class . ':ajaxEdit')->setName('ajax-teams-edit');
        $this->post('/teams/delete', \ARHunt\Team\Controller\TeamController::class . ':ajaxDelete')->setName('ajax-teams-delete');
        $this->post('/steps/add', \ARHunt\Step\Controller\StepController::class . ':ajaxAdd')->setName('ajax-steps-add');
        $this->post('/steps/edit', \ARHunt\Step\Controller\StepController::class . ':ajaxEdit')->setName('ajax-steps-edit');
        $this->post('/steps/delete', \ARHunt\Step\Controller\StepController::class . ':ajaxDelete')->setName('ajax-steps-delete');
        $this->post('/steps/move', \ARHunt\Step\Controller\StepController::class . ':ajaxMove')->setName('ajax-steps-move');
        $this->post('/questions/add', \ARHunt\Question\Controller\QuestionController::class . ':ajaxAdd')->setName('ajax-questions-add');
        $this->post('/questions/edit', \ARHunt\Question\Controller\QuestionController::class . ':ajaxEdit')->setName('ajax-questions-edit');
        $this->post('/questions/delete', \ARHunt\Question\Controller\QuestionController::class . ':ajaxDelete')->setName('ajax-questions-delete');
        $this->post('/choices/add', \ARHunt\Choice\Controller\ChoiceController::class . ':ajaxAdd')->setName('ajax-choices-add');
        $this->post('/choices/edit', \ARHunt\Choice\Controller\ChoiceController::class . ':ajaxEdit')->setName('ajax-choices-edit');
        $this->post('/choices/delete', \ARHunt\Choice\Controller\ChoiceController::class . ':ajaxDelete')->setName('ajax-choices-delete');
        $this->post('/choices/toggle', \ARHunt\Choice\Controller\ChoiceController::class . ':ajaxToggle')->setName('ajax-choices-toggle');
    })->add(new \ARHunt\Signin\Middleware\SigninMiddleware($app->getContainer()));
})->add(new \Stdlib\Middleware\JSONDecodeRequestBodyMiddleware);

$app->group('', function() use ($app) {
    $this->get('/', \ARHunt\Root\Controller\RootController::class . ':root')->setName('root');
    $this->get('/contact-us', \ARHunt\Home\Controller\HomeController::class . ':contactUs')->setName('contact-us');
    $this->get('/verify-email', \ARHunt\Signup\Controller\VerifyEmailController::class . ':verifyEmail')->setName('verify-email');

    $this->group('', function() {
        $this->get('/signin', \ARHunt\Signin\Controller\SigninController::class . ':signin')->setName('signin');
        $this->get('/signup', \ARHunt\Signup\Controller\SignupController::class . ':signup')->setName('signup');
    })->add(new \ARHunt\Signin\Middleware\NoSigninMiddleware($app->getContainer()));

    $this->group('', function() {
        $this->get('/settings', \ARHunt\Setting\Controller\SettingController::class . ':settings')->setName('settings');
        $this->get('/signout', \ARHunt\Signin\Controller\SignoutController::class . ':signout')->setName('signout');
        $this->get('/home', \ARHunt\Home\Controller\HomeController::class . ':home')->setName('home');
        $this->get('/hunts/play', \ARHunt\Hunt\Controller\HuntController::class . ':listPlay')->setName('hunts-list-play');
        $this->get('/hunts/make', \ARHunt\Hunt\Controller\HuntController::class . ':listMake')->setName('hunts-list-make');
        $this->get('/profile[/{nickname}]', \ARHunt\User\Controller\UserController::class . ':profile')->setName('profile');
        $this->get('/hunts/add', \ARHunt\Hunt\Controller\HuntController::class . ':add')->setName('hunts-add');
        $this->get('/hunts/view/{id}', \ARHunt\Hunt\Controller\HuntController::class . ':view')->setName('hunts-view');
        $this->get('/hunts/edit/{id}', \ARHunt\Hunt\Controller\HuntController::class . ':edit')->setName('hunts-edit');
        $this->get('/hunts/play/{id}', \ARHunt\Hunt\Controller\PlayController::class . ':play')->setName('hunts-play');
        $this->get('/hunts/make/{id}', \ARHunt\Hunt\Controller\MakeController::class . ':make')->setName('hunts-make');
        $this->get('/hunts/result/{id}', \ARHunt\Hunt\Controller\ResultController::class . ':result')->setName('hunts-result');
        $this->get('/media/list/{hunt}', \ARHunt\Media\Controller\MediaController::class . ':listMedia')->setName('media-list');
        $this->post('/media/upload', \ARHunt\Media\Controller\MediaController::class . ':upload')->setName('media-upload');
        $this->get('/media/download/{id}', \ARHunt\Media\Controller\MediaController::class . ':download')->setName('media-download');
        $this->get('/teams/list/{hunt}', \ARHunt\Team\Controller\TeamController::class . ':listTeams')->setName('teams-list');
        $this->get('/teams/add/{hunt}', \ARHunt\Team\Controller\TeamController::class . ':add')->setName('teams-add');
        $this->get('/teams/edit/{id}', \ARHunt\Team\Controller\TeamController::class . ':edit')->setName('teams-edit');
        $this->get('/steps/list/{team}', \ARHunt\Step\Controller\StepController::class . ':listSteps')->setName('steps-list');
        $this->get('/steps/add/{team}', \ARHunt\Step\Controller\StepController::class . ':add')->setName('steps-add');
        $this->get('/steps/edit/{team}/{sequence}', \ARHunt\Step\Controller\StepController::class . ':edit')->setName('steps-edit');
        $this->get('/questions/list/{team}/{sequence}', \ARHunt\Question\Controller\QuestionController::class . ':listQuestions')->setName('questions-list');
    })->add(new \ARHunt\Signin\Middleware\SigninMiddleware($app->getContainer()));
});

session_start();

$app->run();