<?php
$app->get('/', function () use ($app) {
    $app->pass();
})->name('home');

$app->get("/private", $authenticate($app), function () use ($app) {
   echo 'Access granted!';
   //$app->pass();
});

$app->get('/generateUrls', function () use ($bcms) {
        
    $bcms['MenuController']->generateUrls();
    
});

$app->get('(:parts+)', function ($parts) use ($bcms) {
        
    $bcms['page'] = $bcms['PageController']->find($parts);
    
    if(!$bcms['page']) {
        $bcms['app']->notFound();
    }
    
    # HTTP cache
    //$bcms['app']->lastModified(1355857675); // or $app->etag('unique-id');
    //$bcms['app']->expires('+1 week');
    
    $template = $bcms['page']->template ? $bcms['page']->template : 'default.twig';
    
    $bcms['app']->render('pages/'.$template, array('page' => $bcms['page']));

});