<?php

$app->get('/generateUrls', function () use ($bcms) {
    $bcms['PageController']->generateUrls();
});

$app->get('/has_childs', function () use ($bcms) {
    $bcms['PageController']->hasChilds();
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
    
})->name('pages.edit');

// Save edited page
$app->post('/admin/pages/edit/:id', $authenticate($bcms['app']), function ($id) use ($bcms)
{
    $page = $bcms['PageController']->findById($id);
    
    if($page)
    {
        // Get request object
        $req = $bcms['app']->request();
                
        //$page->parent = $req->post('parent');
        $page->set('parent', $req->post('parent'));
        $page->name_menu = $req->post('name_menu');
        $page->name_url = $req->post('name_url');
        $page->content = $req->post('content');
        $page->template = $req->post('template');
        $page->name_title = $req->post('name_title');
        $page->name_page = $req->post('name_page');
        $page->redirect_url = $req->post('redirect_url');
        //$page->set_expr('date_changed', 'NOW()');
        $page->save();
        
        $bcms['PageController']->generateUrls();
        
        $bcms['app']->flash('success', 'Success! Page is saved.');
    }
    else
    {
        $bcms['app']->flash('error', 'Page is not found;');
    }
    
    $bcms['app']->redirect($bcms['app']->urlFor('pages.edit', array('id' => $id)));
    
});

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

// Save edited page
$app->post('/admin/pages/add', $authenticate($bcms['app']), function () use ($bcms)
{
    $page = ORM::for_table('b_pages')->create();
    
        // Get request object
        $req = $bcms['app']->request();
                
        //$page->parent = $req->post('parent');
        $page->set('parent', $req->post('parent'));
        $page->name_menu = $req->post('name_menu');
        $page->name_url = $req->post('name_url');
        $page->content = $req->post('content');
        $page->template = $req->post('template');
        $page->name_title = $req->post('name_title');
        $page->name_page = $req->post('name_page');
        $page->redirect_url = $req->post('redirect_url');
        $page->order = ORM::for_table('b_pages')->where('parent', $page->parent)->max('order');
        //$page->set_expr('date_created', 'NOW()');
        $page->save();
        
        $bcms['PageController']->generateUrls();
        
        $bcms['app']->flash('success', 'Success! Page is created.');
   
    $bcms['app']->redirect($bcms['app']->urlFor('pages-list'));
    
});


// Delete page
$app->get('/admin/pages/delete/:id', $authenticate($bcms['app']), function ($id) use ($bcms)
{
    $page = $bcms['PageController']->findById($id);
    $countChilds = ORM::for_table('b_pages')->where('parent', $id)->count();
    
    if($page and $countChilds)
    {
        $bcms['app']->flash('error', 'You can\'t delete page wich has childs.');
    }
    else
    {
        $page->delete();
        $bcms['app']->flash('info', 'Page deleted.');
    }
    
    $bcms['app']->redirect($bcms['app']->urlFor('pages-list'));
    
})->name('pages.delete');


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
    
    $bcms['app']->render('pages/'.$template, array('page' => $bcms['page']));

});