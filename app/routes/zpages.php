<?php

$app->get('/generateUrls', function () use ($bcms) {
    $bcms['PageController']->generateUrls();
});

$app->get('/has_childs', function () use ($bcms) {
    $bcms['PageController']->hasChilds();
});

$app->get('/fixOrder', function () use ($bcms) {
    $bcms['PageController']->fixOrder();
});


$app->get('/', function () use ($app) {
    $app->pass();
})->name('home');

// Admin pages list
$app->get('/admin/pages', $authenticate($bcms['app']), function () use ($bcms)
{
    $bcms['page'] = array('title' => 'bcms');
    $bcms['title'] = 'Pages list';
    $bcms['app']->render('admin/pages-list.twig');
    
})->name('pages-list');

// Add new page
$app->get('/admin/pages/add', $authenticate($bcms['app']), function () use ($bcms)
{
    $bcms['title'] = 'Add new page';
    $templates = array();
    foreach (glob("../app/view/pages/*.twig") as $filename)
    {
        $templates[] = basename($filename);
    }
    $bcms['templates'] = $templates;
    
    $bcms['app']->render('admin/pages-edit.twig');
    
})->name('pages.add');

// Save new page
$app->post('/admin/pages/add', $authenticate($bcms['app']), function () use ($bcms)
{
    $page = ORM::for_table('b_pages')->create();
    
        // Get request object
        $req = $bcms['app']->request();
        
        if(!$req->post('name_menu') or !$req->post('name_url'))
        {
            $bcms['app']->flash('error', 'Name or url is not defined.');
            $bcms['app']->redirect($bcms['app']->urlFor('pages-add'));
        }
        
        //$page->parent = $req->post('parent');
        $page->set('parent', $req->post('parent'));
        $page->name_menu = $req->post('name_menu');
        $page->name_url = $req->post('name_url');
        $page->content = $req->post('content');
        $page->template = $req->post('template');
        $page->name_title = $req->post('name_title');
        $page->name_page = $req->post('name_page');
        $page->redirect_url = $req->post('redirect_url');
        $page->order = $req->post('order') ? $req->post('order') : ORM::for_table('b_pages')->where('parent', $page->parent)->max('order');
        $page->set_expr('date_created', "datetime('now')");
        $page->save();
        
        $bcms['PageController']->generateUrls($page->parent);
        $bcms['PageController']->fixOrder($page->parent);
        
        $bcms['app']->flash('success', 'Success! Page is created.');
   
    $bcms['app']->redirect($bcms['app']->urlFor('pages-list'));
    
})->name('pages-add');;

// Edit pages
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
    
    $bcms['title'] = 'Edit page "' . $bcms['page']['name_menu']. '"';
    
    $bcms['app']->render('admin/pages-edit.twig');
    
})->name('pages-edit');

// Save edited page
$app->post('/admin/pages/edit/:id', $authenticate($bcms['app']), function ($id) use ($bcms)
{
    $page = $bcms['PageController']->findById($id);
    
    if($page)
    {
        // Get request object
        $req = $bcms['app']->request();

        if($page->parent !== $req->post('parent') && $req->post('parent'))
        {
            //$page->parent = $req->post('parent');
            $page->set('parent', $req->post('parent'));
        }

        $page->name_menu = $req->post('name_menu');
        $page->name_url = $req->post('name_url');
        $page->content = $req->post('content');
        $page->template = $req->post('template');
        $page->name_title = $req->post('name_title');
        $page->name_page = $req->post('name_page');
        $page->redirect_url = $req->post('redirect_url');

        if($page->order !== $req->post('order'))
        {
            $page->order = $req->post('order');
            $bcms['PageController']->movePage($page->order, $page->parent);
        }

        $page->set_expr('date_changed', "datetime('now')");        
        
        $page->save();
        
        if($page->id > 0)
        {
            $bcms['PageController']->generateUrls($page->parent);
            $bcms['PageController']->fixOrder($page->parent);
        }
        
        $bcms['app']->flash('success', 'Success! Page is saved.');
    }
    else
    {
        $bcms['app']->flash('error', 'Page is not found;');
    }
    
    $bcms['app']->redirect($bcms['app']->urlFor('pages-edit', array('id' => $id)));
    
});

// Delete page
$app->get('/admin/pages/delete/:id', $authenticate($bcms['app']), function ($id) use ($bcms)
{
    $page = $bcms['PageController']->findById($id);
    $countChilds = ORM::for_table('b_pages')->where('parent', $id)->count();

    if(!$id)
    {
        $bcms['app']->flash('error', 'You can\'t delete this page.');
    }
    elseif($page and $countChilds)
    {
        $bcms['app']->flash('error', 'You can\'t delete page wich has childs.');
    }
    else
    {
        $page->delete();
        
        $bcms['PageController']->fixOrder($page->parent);
        
        $bcms['app']->flash('info', 'Page deleted.');
    }
    
    $bcms['app']->redirect($bcms['app']->urlFor('pages-list'));
    
})->name('page-delete');


// Delete many pages
$app->post('/admin/pages/delete_many', $authenticate($bcms['app']), function () use ($bcms)
{

    $req = $bcms['app']->request();

    if($req->post('pages'))
    {
        // Get request object
                
        print_r($req->post('pages'));
        
        $bcms['app']->flash('info', 'Pages have been deleted.');
    }
    else
    {
        $bcms['app']->flash('error', 'Nothing selected;');
    }
    
    //$bcms['app']->redirect($bcms['app']->urlFor('pages-list'));
    
});



// Edit pages
$app->get('/admin/pages/move/:id/:where', $authenticate($bcms['app']), function ($id, $where) use ($bcms)
{
    $result = $bcms['PageController']->move($id, $where);
    
    if($result)
    {
        $bcms['app']->flash('info', 'Page is moved.');
    }
    else
    {
        $bcms['app']->flash('error', 'Error!');
    }

    $bcms['app']->redirect($bcms['app']->urlFor('pages-list'));
    
});

/* Public page */
$app->get('(:parts+)', function ($parts) use ($bcms) {
        
    $bcms['page'] = $bcms['PageController']->findByUrl($parts);
    
    if(!$bcms['page']) {
        $bcms['app']->notFound();
    }

    if($bcms['page']->redirect_url) {
        $bcms['app']->redirect($bcms['page']->redirect_url);
    }
    
    // HTTP cache
    //$bcms['app']->lastModified(strtotime($bcms['page']->date_changed)); // or $app->etag('unique-id');
    //$bcms['app']->expires('+1 week');
    
   
    
    $template = $bcms['page']->template ? $bcms['page']->template : 'default.twig';
    
    $bcms['app']->render('pages/'.$template, array('page' => $bcms['page'])); //, array('page' => $bcms['page'])

});