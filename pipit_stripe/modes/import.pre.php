<?php
    $message = '';

    function import_plans() {
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
    
    
    
    
    function import_products() {
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





    $plans_result = import_products();
    $products_result = import_plans();