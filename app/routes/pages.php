<?php
$app->get('/', function () use ($app) {
    $app->pass();
})->name('home');

$app->get("/private", $authenticate($app), function () use ($app) {
   echo 'Access granted!';
   //$app->pass();
});

$app->get('(:parts+)', function ($parts) use ($bcms) {
    
    $bcms['page'] = $bcms['PageController']->find($parts);
    
    if(!$bcms['page']) {
        $bcms['app']->notFound();
    }
    
    $template = $bcms['page']->template ? $bcms['page']->template : 'index.twig';
    
    $bcms['app']->render('pages/'.$template, array('page' => $bcms['page']));

});