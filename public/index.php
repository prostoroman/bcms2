<?php

$time_start = microtime(true); //place this before any script you want to calculate time

/*/sample script
for($i=0; $i<1000; $i++){
 //do anything
}
*/

require '../vendor/autoload.php';

require '../app/config.php';
require '../app/start.php';


foreach (glob("../app/routes/*.php") as $filename)
{
    require $filename;
}

// Total Execution Time
$app->run();

    $queryLog = ORM::get_query_log();
    echo '<pre>';
    print_r($queryLog);
    echo '</pre>';

$time_end = microtime(true);
$execution_time = ($time_end - $time_start)/60; //dividing with 60 will give the execution time in minutes other wise seconds
echo '<b>Total Execution Time:</b> '.$execution_time.' Mins'; //execution time of the script

?>