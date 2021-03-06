<?php

// Prepare app
$app = new \Slim\Slim($bcms['slim.config']);

// Prepare TWIG
\Slim\Extras\Views\Twig::$twigOptions = $bcms['twig.config'];

$app->view(new \Slim\Extras\Views\Twig());

$bcms['app'] = $app;

$twig = $app->view()->getEnvironment();
$twig->addGlobal('bcms', $bcms);

$bcms['PageController'] = $bcms->share(function ($bcms) {
    return new PageController($bcms);
});

$bcms['MenuController'] = $bcms->share(function ($bcms) {
    return new MenuController($bcms);
});

foreach (glob("../app/model/*.php") as $filename)
{
    require $filename;
}

foreach (glob("../app/controller/*.php") as $filename)
{
    require $filename;
}
