<?php

echo $HTML->title_panel([
    'heading' => $Lang->get('Stripe: Import Products and Plans'),
], $CurrentUser);




$Smartbar = new PerchSmartbar($CurrentUser, $HTML, $Lang);

    $Smartbar->add_item([
        'active' => true,
        'type'  => 'breadcrumb',
        'links' => [
            [
                'title' => 'Products',
                'link'  => $API->app_nav().'/products',
            ],
            [
                'title' => 'Import',
                'link'  => $API->app_nav().'/import#',
            ]
        ],
    ]);

echo $Smartbar->render();




echo $HTML->heading2('Products');
switch($products_result['result']) {
    case 'OK':
        echo $HTML->success_block($products_result['message'], '');
        break;

    case 'FAILED':
        echo $HTML->failure_block($products_result['message'], '');
        break;

    default:
        echo $HTML->warning_block($products_result['message'], '');
}



echo $HTML->heading2('Plans');
switch($plans_result['result']) {
    case 'OK':
        echo $HTML->success_block($plans_result['message'], '');
        break;

    case 'FAILED':
        echo $HTML->failure_block($plans_result['message'], '');
        break;

    default:
        echo $HTML->warning_block($plans_result['message'], '');
}