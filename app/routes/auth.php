<?php

//session_cache_limiter(false);
//session_start();

$app->add(new \Slim\Middleware\SessionCookie($bcms['sessioncookie.config']));

$authenticate = function ($app) {
    return function () use ($app) {
        if (!isset($_SESSION['user'])) {
            $_SESSION['urlRedirect'] = $app->request()->getPathInfo();
            $app->flash('error', 'Login required');

            // Get request object
             $req = $app->request();
             
             //Get root URI
             $rootUri = $req->getRootUri();
            
            $app->redirect($rootUri.'/login');
        }
    };
};

$app->hook('slim.before.dispatch', function() use ($app) {
   $user = null;
   if (isset($_SESSION['user'])) {
      $user = $_SESSION['user'];
   }
   $app->view()->setData('user', $user);
});


$app->get("/logout", function () use ($app) {
   unset($_SESSION['user']);
   $app->view()->setData('user', null);
   
   $app->redirect($app->urlFor('home'));
   
})->name('logout');

$app->get("/login", function () use ($app) {
   $flash = $app->view()->getData('flash');

   
   // Get request object
    $req = $app->request();
    
    //Get root URI
    $rootUri = $req->getRootUri();
    
    //Get resource URI
    $resourceUri = $req->getResourceUri();
   
   $error = '';
   if (isset($flash['error'])) {
      $error = $flash['error'];
   }

   $urlRedirect = '/';

   if ($app->request()->get('r') && $app->request()->get('r') != '/logout' && $app->request()->get('r') != '/login') {
      $_SESSION['urlRedirect'] = $app->request()->get('r');
   }

   if (isset($_SESSION['urlRedirect'])) {
      $urlRedirect = $_SESSION['urlRedirect'];
   }

   $email_value = $email_error = $password_error = '';

   if (isset($flash['email'])) {
      $email_value = $flash['email'];
   }

   if (isset($flash['errors']['email'])) {
      $email_error = $flash['errors']['email'];
   }

   if (isset($flash['errors']['password'])) {
      $password_error = $flash['errors']['password'];
   }
   
   $app->render('login.twig', array('error' => $error, 'email_value' => $email_value, 'email_error' => $email_error, 'password_error' => $password_error, 'urlRedirect' => $urlRedirect));

})->name('login');

$app->post("/login", function () use ($app) {
    $email = $app->request()->post('email');
    $password = $app->request()->post('password');

    $passwordEncoded = bcrypt($password);
   
    $errors = array();

    if ($email != "roman@bs1.ru") {
        $errors['email'] = "Email is not found.";
    } else if (!bcrypt_verify('123', $passwordEncoded)) {
        $app->flash('email', $email);
        $errors['password'] = "Password does not match.";
    }

    if (count($errors) > 0) {
        $app->flash('errors', $errors);
        $app->redirect($app->urlFor('login'));
    }

    $_SESSION['user'] = $email;

       // Get request object
    $req = $app->request();
    
    //Get root URI
    $rootUri = $req->getRootUri();
    
    if (isset($_SESSION['urlRedirect'])) {
       $tmp = $_SESSION['urlRedirect'];
       unset($_SESSION['urlRedirect']);
       $app->redirect($rootUri.$tmp);
    }

    $app->redirect($app->urlFor('home'));
});
