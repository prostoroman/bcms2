<?php

$app->get('/admin/users/install', $authenticate($bcms['app']), function () use ($bcms) {
    $bcms['users']->install();
});

$app->get('/admin/users', $authenticate($bcms['app']), function () use ($bcms) {
    
    $bcms['title'] = 'Users';
    $bcms['app']->render('admin/users.twig');
    
})->name('users');

// Save edited users
$app->post('/admin/users', $authenticate($bcms['app']), function () use ($bcms)
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
            $option = $bcms['users']->edit($key, $value);
        }    
        
        $bcms['app']->flash('success', 'Users saved');
    }
    
    $bcms['app']->redirect($bcms['app']->urlFor('users'));
    
});

// Add option
$app->post('/admin/users/add', $authenticate($bcms['app']), function () use ($bcms)
{
    // Get request object
    $req = $bcms['app']->request();
    $data = $req->post();
    print_r($data);
    
    if(!$req->post('username'))
    {
        $bcms['app']->flash('error', 'Username is required');
    }
    else
    {
        $bcms['users']->add($data);
        $bcms['app']->flash('success', 'User is added.');
    }
    
    $bcms['app']->redirect($bcms['app']->urlFor('users'));        

});

$app->get('/admin/users/delete/:id', function ($id) use ($bcms) {
    $bcms['users']->delete($id);
    $bcms['app']->flash('success', 'User is deleted.');
    $bcms['app']->redirect($bcms['app']->urlFor('users'));
});