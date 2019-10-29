<?php
// JSON payload
$payload = @file_get_contents('php://input');

// get Stripe event and verify signature
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = pipit_stripe_webhook_event($payload, $sig_header);

# Debug
# echo '<pre>' . print_r($event, 1) . '</pre>';
$data = $event->data->object;


// handle events
switch($event->type) {
    case 'invoice.payment_succeeded':
        // you can use pipit_members app to add/remove tags 
        // e.g. pipit_members_add_tag('monthly', $data->customer_email);
        break;
        
    case 'invoice.payment_failed':
        break;
    
    case 'customer.subscription.deleted':
        break;
}



// Return a response to acknowledge receipt of the event
http_response_code(200); // PHP 5.4 or greater

?>