<?php

#echo '<pre>' . print_r($product, 1) . '</pre>';
#echo '<pre>' . print_r($plans, 1) . '</pre>';

echo $HTML->title_panel([
    'heading' => $Lang->get('Stripe Products') . ': ' . $product['name'],
], $CurrentUser);



if($message) {
    echo $HTML->warning_block($message, '');
}



$Smartbar = new PerchSmartbar($CurrentUser, $HTML, $Lang);

    $Smartbar->add_item([
        'active' => true,
        'type'  => 'breadcrumb',
        'links' => [
            [
                'title' => 'Products',
                'link'  => $return_nav,
                'translate' => false,
            ],
            [
                'title' => $product['name'],
                'link'  => $return_nav . '/view/?id=' . $product['id'],
                'translate' => false,
            ]
        ],
    ]);

echo $Smartbar->render();



// list product details
echo $summary_html;



// list plans
echo $HTML->heading2('Product Plans');
if(!$plans) {
    echo $HTML->info_block($product['name'] . " has no plans.", '');
} else {
    $AdminListing = new PerchAdminListing($CurrentUser, $HTML, $Lang, $Paging);
    
        $AdminListing->add_col([
            'title'     => 'Nickname',
            'value'     => function($item) {
                return $item['nickname'];
            },
            // 'edit_link' => 'view',
        ]);

        

        $AdminListing->add_col([
            'title'     => 'Interval',
            'value'     => function($item) {
                return $item['interval'];
            },
        ]);


        $AdminListing->add_col([
            'title'     => 'Currency',
            'value'     => function($item) {
                return $item['currency'];
            },
        ]);

        $AdminListing->add_col([
            'title'     => 'Created',
            'value'     => function($item) {
                return date('d/m/Y - H:i:s', strtotime($item['created']));
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


        $AdminListing->add_col([
            'title'     => 'Live Mode',
            'value'     => function($item) {
                $icon = PerchUI::icon('core/cancel', 16, null, 'icon-status-alert');

                if($item['livemode']) {
                    $icon = PerchUI::icon('core/circle-check', 16, null, 'icon-status-success');
                }

                return $icon;
            },
        ]);

        /*$AdminListing->add_misc_action([
            'title'  => 'View',
            'class'  => 'info',
            'path'   => function($item){
                return 'view/?id=' . $item['id'];
            },
        ]);*/
    
    
    echo $AdminListing->render($plans);
}