<?php
    include(__DIR__.'/_version.php');
    $this->register_app('pipit_stripe', 'Stripe', 99, 'Stripe app', PIPIT_STRIPE_VERSION);
    $this->require_version('pipit_stripe', '3.0');
    
    include(__DIR__.'/lib/vendor/autoload.php');
    
    spl_autoload_register(function($class_name){
        if (strpos($class_name, 'PipitStripe_')===0) {
            include(PERCH_PATH.'/addons/apps/pipit_stripe/lib/'.$class_name.'.class.php');
            return true;
        }
        return false;
    });
    
    
    