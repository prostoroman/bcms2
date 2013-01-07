<?php

$app->get('/admin/files', $authenticate($bcms['app']), function () use ($bcms)
{
    $bcms['title'] = 'Files';
    $bcms['app']->render('admin/files.twig');
});
