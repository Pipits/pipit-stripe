<?php
    $Products = new PipitStripe_Products();
    $Plans = new PipitStripe_Plans();
    $return_nav = $API->app_nav().'/products';

    if (!PerchUtil::get('id')) {
        PerchUtil::redirect($return_nav);
    }
    
    $product = $Products->get_product(PerchUtil::get('id'));
    $plans = $Plans->get_plans_for(PerchUtil::get('id'));
    $message = '';


    $Template->set('stripe/admin/product_summary.html', 'content');
    $summary_html = $Template->render($product);