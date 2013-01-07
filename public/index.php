<?php

$time_start = microtime(true); //place this before any script you want to calculate time

require '../app/vendor/autoload.php';
require '../app/config.php';
require '../app/start.php';


foreach (glob("../app/routes/*.php") as $filename)
{
    require $filename;
}

// Total Execution Time
$app->run();


/* STATISTIC //////////////////////////////////
$queryLog = ORM::get_query_log();
echo '<pre>';
print_r($queryLog);
echo '</pre>';

$time_end = microtime(true);
$execution_time = ($time_end - $time_start)/60; //dividing with 60 will give the execution time in minutes other wise seconds
function convert($size)
 {
    $unit=array('B','KB','MB','GB','TB','PB');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).$unit[$i];
 }

echo '<b>Memory usage:</b> ' . convert(memory_get_peak_usage(true)).'<br />'; // 123 kb
echo '<b>Total Execution Time:</b> '.$execution_time.' Mins<br />'; //execution time of the script
echo '<b>Page Creation Time:</b> '.date('H:i:s e').'<br/><br/>'; //execution time of the script
*/
?>