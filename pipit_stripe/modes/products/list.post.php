<?php

echo $HTML->title_panel([
    'heading' => $Lang->get('Stripe Products'),
    'button'  => [
        'text' => $Lang->get('Update from Stripe'),
        'link' => $API->app_nav().'/import',
        'icon' => 'ext/o-sync',
    ]
], $CurrentUser);



if($message) {
    echo $HTML->warning_block($message, '');
}



$Smartbar = new PerchSmartbar($CurrentUser, $HTML, $Lang);

    $Smartbar->add_item([
        'title' => 'Products',
        'link'  => $API->app_nav().'/products',
        'icon'  => 'ext/o-shirt',
        'active' => true
    ]);

echo $Smartbar->render();






$AdminListing = new PerchAdminListing($CurrentUser, $HTML, $Lang, $Paging);

    $AdminListing->add_col([
        'title'     => 'Name',
        'value'     => function($item) {
            return $item['name'];
        },
        // 'edit_link' => 'view',
    ]);

    $AdminListing->add_col([
        'title'     => 'Created',
        'value'     => function($item) {
            return date('d/m/Y - H:i:s', strtotime($item['created']));
        },
    ]);

    $AdminListing->add_col([
        'title'     => 'Updated',
        'value'     => function($item) {
            return date('d/m/Y - H:i:s', strtotime($item['updated']));
        },
    ]);

    $AdminListing->add_col([
        'title'     => 'Active',
        'value'     => function($item) {
            $icon = PerchUI::icon('core/cancel', 16, null, 'icon-status-alert');

            if($item['active']) {
                $icon = PerchUI::icon('core/circle-check', 16, null, 'icon-status-success');
            }

            return $icon;
        },
    ]);

    $AdminListing->add_misc_action([
        'title'  => 'View',
        'class'  => 'info',
        'path'   => function($item){
            return 'view/?id=' . $item['id'];
        },
    ]);
    

echo $AdminListing->render($products);

