<?php

$app->get('/generateUrls', function () use ($bcms) {
        
    $bcms['MenuController']->generateUrls();
    
});

$app->get('/has_childs', function () use ($bcms) {
        
    $bcms['MenuController']->hasChilds();
    
});

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
    
    $pages = $bcms['MenuController']->menuTree();
    //print_r($pages);
    
    // HTTP cache
    //$bcms['app']->lastModified(strtotime($bcms['page']->date_changed)); // or $app->etag('unique-id');
    //$bcms['app']->expires('+1 week');
    
    $template = $bcms['page']->template ? $bcms['page']->template : 'default.twig';
    
    $bcms['app']->render('pages/'.$template, array('page' => $bcms['page']));

});