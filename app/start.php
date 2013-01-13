<?php

// Prepare app
$app = new \Slim\Slim($bcms['slim.config']);

// Prepare TWIG
\Slim\Extras\Views\Twig::$twigOptions = $bcms['twig.config'];

foreach (glob("../app/lib/*.php") as $filename)
{
    require $filename;
}
/*
if ( ! function_exists('glob_recursive'))
{
    // Does not support flag GLOB_BRACE
   
    function glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
       
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
        {
            $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
        }
       
        return $files;
    }
}

foreach (glob_recursive("../app/plugins/*.php") as $filename)
{
    require $filename;
}


$dir = '../app/plugins/';
$dirs = array_diff(scandir($dir), array('.', '..'));

foreach ($dirs as $filename)
{
    if(is_dir($dir.$filename))
    echo $filename.'<br />';
}
*/
$app->view(new \Slim\Extras\Views\Twig());

$bcms['app'] = $app;

$twig = $app->view()->getEnvironment();
$twig->addExtension(new Twig_Extension_StringLoader());
//$twig->addExtension(new Twig_Extensions_Extension_I18n());

$twig->addGlobal('bcms', $bcms);

$bcms['PageController'] = $bcms->share(function ($bcms) {
    return new PageController($bcms);
});

$bcms['options'] = $bcms->share(function ($bcms) {
    return new OptionsController($bcms);
});

$bcms['users'] = $bcms->share(function ($bcms) {
    return new UsersController($bcms);
});
