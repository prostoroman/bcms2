<?php

// Prepare app
$app = new \Slim\Slim($bcms['slim.config']);

// Prepare TWIG
\Slim\Extras\Views\Twig::$twigOptions = $bcms['twig.config'];

foreach (glob("../app/lib/*.php") as $filename)
{
    require $filename;
}


$app->view(new \Slim\Extras\Views\Twig());

$bcms['app'] = $app;

$twig = $app->view()->getEnvironment();
$twig->addExtension(new Twig_Extension_StringLoader());
//$twig->addExtension(new Twig_Extensions_Extension_I18n());
$twig->addGlobal('bcms', $bcms);

$bcms['PageController'] = $bcms->share(function ($bcms) {
    return new PageController($bcms);
});

