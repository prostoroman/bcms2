<?php

$app->get('/generateUrls', function () use ($bcms) {
        
    $bcms['MenuController']->generateUrls();
    
});