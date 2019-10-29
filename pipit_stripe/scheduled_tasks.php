<?php
include(__DIR__.'/lib/vendor/autoload.php');

spl_autoload_register(function($class_name){
    if (strpos($class_name, 'PipitStripe_')===0) {
        include(PERCH_PATH.'/addons/apps/pipit_stripe/lib/'.$class_name.'.class.php');
        return true;
    }
    return false;
});

$minutes = 60;

PerchScheduledTasks::register_task('pipit_stripe', 'import_plans', $minutes, 'import_plans');
PerchScheduledTasks::register_task('pipit_stripe', 'import_products', $minutes, 'import_products');



/**
 * imports Stripe plans and saves them to a JSON file
 */
function import_plans($last_run_date) {
    $result['result'] = 'WARNING';
    $result['message'] = 'No message set';

    if(!defined('PIPIT_STRIPE_SECRET_KEY')) {
        $result['result'] = 'FAILED';
        $result['message'] = 'Secret key not set';
        return $result;
    }

    $Plans = new PipitStripe_Plans();
    $result = $Plans->get_from_stripe(100, true);
    if($result['result'] != 'OK') return $result;


    $result['result'] = 'OK';
    $result['message'] = 'Plans fetched and saved.';
    return $result;
}



/**
 * imports Stripe products and saves them to a JSON file
 */
function import_products($last_run_date) {
    $result['result'] = 'WARNING';
    $result['message'] = 'No message set';

    if(!defined('PIPIT_STRIPE_SECRET_KEY')) {
        $result['result'] = 'FAILED';
        $result['message'] = 'Secret key not set';
        return $result;
    }

    $Products = new PipitStripe_Products();
    $result = $Products->get_from_stripe(100, true);
    if($result['result'] != 'OK') return $result;


    $result['result'] = 'OK';
    $result['message'] = 'Products fetched and saved.';
    return $result;
}