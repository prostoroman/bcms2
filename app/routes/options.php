<?php

$app->get('/admin/options/install', function () use ($bcms) {
    $bcms['options']->install();
});

$app->get('/admin/options', $authenticate($bcms['app']), function () use ($bcms) {
    
    $bcms['title'] = 'Options';
    $bcms['app']->render('admin/options.twig');
    //echo $bcms['options']->site_name;
    
})->name('options');

// Save edited options
$app->post('/admin/options', $authenticate($bcms['app']), function () use ($bcms)
{
    // Get request object
    $req = $bcms['app']->request();
    $postVars = $req->post();
    
    if(empty($postVars))
    {
        $bcms['app']->flash('info', 'Nothing changed.');
    }
    else
    {
        foreach($postVars as $key => $value)
        {
            $option = $bcms['options']->edit($key, $value);
        }    
        
        $bcms['app']->flash('success', 'Options saved');
    }
    
    $bcms['app']->redirect($bcms['app']->urlFor('options'));
    
});

// Add option
$app->post('/admin/options/add', $authenticate($bcms['app']), function () use ($bcms)
{
    // Get request object
    $req = $bcms['app']->request();
    
    if(!$req->post('name'))
    {
        $bcms['app']->flash('error', 'Option name is required');
    }
    else
    {
        $option = ORM::for_table('b_options')->create();    
        $option->name = $req->post('name');
        $option->value = $req->post('value');
        $option->description = $req->post('description');
        $option->save();
        $bcms['app']->flash('success', 'Option is added.');
    }
    
    $bcms['app']->redirect($bcms['app']->urlFor('options'));        

});

$app->get('/admin/options/delete/:name', function ($name) use ($bcms) {
    $bcms['options']->delete($name);
    $bcms['app']->flash('success', 'Option is deleted.');
    $bcms['app']->redirect($bcms['app']->urlFor('options'));
});