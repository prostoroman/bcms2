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


$app->get('/admin/pages', $authenticate($bcms['app']), function () use ($bcms)
{
    $bcms['page'] = array('title' => 'bcms');
    $bcms['app']->render('admin/pages.twig');
    
})->name('admin.pages');

$app->get('/admin/pages/edit/:id', $authenticate($bcms['app']), function ($id) use ($bcms)
{
    $page = $bcms['PageController']->findById($id);
    if($page)
    {
        $bcms['page'] = $page->as_array();
        $templates = array();
        foreach (glob("../app/view/pages/*.twig") as $filename)
        {
            $templates[] = basename($filename);
        }
        $bcms['templates'] = $templates;
    }
    else
    {
        $bcms['app']->notFound();
    }
    
    $bcms['app']->render('admin/pages-edit.twig');
    
})->name('admin.pages.edit');

/*
Any page
*/

$app->get('(:parts+)', function ($parts) use ($bcms) {
        
    $bcms['page'] = $bcms['PageController']->findByUrl($parts);
    
    if(!$bcms['page']) {
        $bcms['app']->notFound();
    }
        
    // HTTP cache
    //$bcms['app']->lastModified(strtotime($bcms['page']->date_changed)); // or $app->etag('unique-id');
    //$bcms['app']->expires('+1 week');
    
    $template = $bcms['page']->template ? $bcms['page']->template : 'default.twig';
    
    $bcms['app']->render('pages/'.$template, array('page' => $bcms['page']));

});