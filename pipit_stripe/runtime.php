<?php
    include(__DIR__.'/lib/vendor/autoload.php');

    spl_autoload_register(function($class_name){
        if (strpos($class_name, 'PipitStripe_')===0) {
            include(PERCH_PATH.'/addons/apps/pipit_stripe/lib/'.$class_name.'.class.php');
            return true;
        }
        return false;
    });




    /**
     * Form handler for forms submitted with <perch:form app="pipit_stripe"></perch:form>
     * 
     * @param object $SubmittedForm     PerchAPI_SubmittedForm
     */
    function pipit_stripe_form_handler($SubmittedForm) {
        if ($SubmittedForm->validate()) {
            switch($SubmittedForm->formID) {
                case 'unsubscribe':
                    if(!perch_member_logged_in()) {
                        $SubmittedForm->throw_error('login', 'all');
                    }

                    // get data from submitted form
                    $data = $SubmittedForm->data;
                    #echo '<pre>' . print_r($data, 1) . '</pre>' ;
                    #exit;

                    $result = false;
                    if(isset($data['subscriptionID'])) {
                        $result = pipit_stripe_cancel_subscription($data['subscriptionID'], $data['cancel_at_end'], $data['customer_email']);
                    } elseif(isset($data['planID'])) {
                        $result = pipit_stripe_cancel_customer_plan($data['planID'], $data['cancel_at_end'], $data['customer_email']);
                    }


                    // could not unsubscribe
                    if(!$result) {
                        PerchUtil::debug('Could not unsubscribe' , 'error');
                        $SubmittedForm->throw_error('unsubscribe', 'all');
                    }

                    // has a return url?
                    if(isset($data['return_url'])) {
                        PerchUtil::redirect($data['return_url']);
                    }
                    break;
            }
        }
    }






    
    /**
     * verify webhook signature and fires a Perch event 
     * 
     * @param JSON $payload             Event JSON sent by Stripe
     * @param string $sig_header        Webhook signature send by Stripe
     * @param string $endpoint_secret   Webhook endpoint secret
     * 
     * @return object
     */
    function pipit_stripe_webhook_event($payload, $sig_header, $endpoint_secret = '') {
        if(!defined('PIPIT_STRIPE_SECRET_KEY')) {
            PerchUtil::debug('Stripe secret key not set', 'error');
            return false;
        }

        if(!$endpoint_secret) {
            if(!defined('PIPIT_STRIPE_ENDPOINT_SECRET')) {
                PerchUtil::debug('Stripe secret key not set', 'error');
                return false;
            } else {
                $endpoint_secret = PIPIT_STRIPE_ENDPOINT_SECRET;
            }
        }


        \Stripe\Stripe::setApiKey(PIPIT_STRIPE_SECRET_KEY);

        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Error\SignatureVerification $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }


        $API  = new PerchAPI(1.0, 'pipit_stripe');
        $API->event('pipit_stripe.wh.' . $event->type, $event);

        return $event;
    }







    /**
     * Retrieve a single customer from Stripe
     * 
     * @param string $customerID
     * 
     * @return object
     */
    function pipit_stripe_get_customer($customerID) {
        if(!defined('PIPIT_STRIPE_SECRET_KEY')) {
            PerchUtil::debug('Stripe secret key not set', 'error');
            return false;
        }

        \Stripe\Stripe::setApiKey(PIPIT_STRIPE_SECRET_KEY);
        $customer = \Stripe\Customer::retrieve($customerID);

        return $customer;
    }



    



    /**
     * Fetch customers from Stripe
     * 
     * @param array $opts   Options array for filtering. Refer to Stripe documentation for available options.
     * 
     * @return object
     */
    function pipit_stripe_get_customers($opts=array()) {
        if(!defined('PIPIT_STRIPE_SECRET_KEY')) {
            PerchUtil::debug('Stripe secret key not set', 'error');
            return false;
        }

        \Stripe\Stripe::setApiKey(PIPIT_STRIPE_SECRET_KEY);
        $customer = \Stripe\Customer::all($opts);

        return $customer;
    }







    /**
     * Get a customer's subscriptions
     * 
     * @param string $email     Customer's email address
     * 
     * @return object|boolean
     */
    function pipit_stripe_get_customer_subscriptions($email) {
        $customers = pipit_stripe_get_customers(['email' => $email]);

        if(is_object($customers)) {
            foreach($customers->data as $customer) {
                if($customer->subscriptions->data) return $customer->subscriptions->data;
            }
        }

        return false;
    }




    


    /**
     * Update a Stripe subscription
     * 
     * @param string $subID             The subscription's ID
     * @param boolean $opts             Option arrays. Refer to Stripe documentation for the available options.
     * 
     * @return object                   If a charge is required for the update and the charge fails, this call throws an error, and the subscription update does not go into effect.
     */
    function pipit_stripe_update_subscription($subID, $opts) {
        if(!defined('PIPIT_STRIPE_SECRET_KEY')) {
            PerchUtil::debug('Stripe secret key not set', 'error');
            return false;
        }

        \Stripe\Stripe::setApiKey(PIPIT_STRIPE_SECRET_KEY);
        return \Stripe\Subscription::update($subID, $opts);
    }






    
    /**
     * Cancel a Stripe subscription
     * 
     * @param string $subID             The subscription's ID
     * @param boolean $cancel_at_end    Whether to cancel at end of subscription or immediately
     * 
     */
    function pipit_stripe_cancel_subscription($subID, $cancel_at_end = true) {
        if(!defined('PIPIT_STRIPE_SECRET_KEY')) {
            PerchUtil::debug('Stripe secret key not set', 'error');
            return false;
        }

        \Stripe\Stripe::setApiKey(PIPIT_STRIPE_SECRET_KEY);

        
        if($cancel_at_end) {
            return \Stripe\Subscription::update($subID, ['cancel_at_period_end' => true,]);
        } else {
            $subscription = \Stripe\Subscription::retrieve($subID);
            return $subscription->cancel();
        }
    }







    /**
     * Cancel a customer's subscription given a plan ID
     * 
     * @param string $planID            The plan's ID
     * @param boolean $cancel_at_end    Whether to cancel at end of subscription or immediately
     * @param string $email             The customer's email address
     * 
     * @return object|boolean
     */
    function pipit_stripe_cancel_customer_plan($planID, $cancel_at_end = true, $email = 'member') {
        if(!defined('PIPIT_STRIPE_SECRET_KEY')) {
            PerchUtil::debug('Stripe secret key not set', 'error');
            return false;
        }

        \Stripe\Stripe::setApiKey(PIPIT_STRIPE_SECRET_KEY);

        if($email == 'member') {
            if(perch_member_logged_in()) {
                $email = perch_member_get('email');
            } else {
                PerchUtil::debug('Member is not logged in', 'error');
                return false;
            }
        }

        // get all customer's subscriptions
        $subscriptions = pipit_stripe_get_customer_subscriptions($email);

        // cancel subscription with $planID
        if($subscriptions) {
            foreach($subscriptions as $sub) {
                $subID = $sub->id;
                if($sub->plan->id == $planID) {
                    // cancel
                    return pipit_stripe_cancel_subscription($subID, $cancel_at_end);
                }
            }
        }

        return false;
        
    }


    




    /**
     * Get the active plans for the given API key
     * 
     * @param array $opts
     * @param boolean $return_html
     * 
     */
    function pipit_stripe_plans($opts=[], $return_html=false) {
        $Plans = new PipitStripe_Plans();
        $Util = new PipitStripe_Util();
        $plans = $Plans->get();

        $default_opts = [
            'template' => 'plans/list.html',
            'customer_email' => 'member',
            'return_url' => SITE_URL . '/subscription/success',
            'cancel_url' => SITE_URL . '/subscription/cancel',
        ];

        $opts = array_merge($default_opts, $opts);

        // data for templating
        $data = [
            'customer_email' => $opts['customer_email'],
            'return_url' => $opts['return_url'],
            'cancel_url' => $opts['cancel_url'],
            'publishable_key' => PIPIT_STRIPE_PUBLISHABLE_KEY
        ];

        // get logged-in member's email address
        if($data['customer_email'] == 'member') {
            if(perch_member_logged_in()) {
                $data['customer_email'] = perch_member_get('email');
            } else {
                $data['customer_email'] = '';
                PerchUtil::debug('Member is not logged in.', 'notice');
            }
        }


        // add $data to each plan for templating
        array_walk($plans, function(&$item) use($data) {
            $item = array_merge($item, $data);
        });

        return $Util->template($default_opts, $opts, $plans, $return_html);
    }







    /**
     * Output a single plan
     * @param string @planID
     * @param array $opts
     * @param boolean $return_html
     */
    function pipit_stripe_plan($planID, $opts=[], $return_html=false) {
        $Plans = new PipitStripe_Plans();
        $Util = new PipitStripe_Util();
        $plans = $Plans->get_plan($planID);

        $default_opts = [
            'template' => 'plans/detail.html',
            'customer_email' => 'member',
            'return_url' => SITE_URL . '/subscription/success',
            'cancel_url' => SITE_URL . '/subscription/cancel',
        ];

        $opts = array_merge($default_opts, $opts);

        // data for templating
        $data = [
            'customer_email' => $opts['customer_email'],
            'return_url' => $opts['return_url'],
            'cancel_url' => $opts['cancel_url'],
            'publishable_key' => PIPIT_STRIPE_PUBLISHABLE_KEY
        ];

        // get logged-in member's email address
        if($data['customer_email'] == 'member') {
            if(perch_member_logged_in()) {
                $data['customer_email'] = perch_member_get('email');
            } else {
                $data['customer_email'] = '';
                PerchUtil::debug('Member is not logged in.', 'notice');
            }
        }


        // add $data to each plan for templating
        array_walk($plans, function(&$item) use($data) {
            $item = array_merge($item, $data);
        });

        return $Util->template($default_opts, $opts, [$plans], $return_html);
    }







    /**
     * Get plans for a specific product
     * 
     * @param string $productID
     * @param array $opts
     * @param boolean $return_html
     * 
     */
    function pipit_stripe_plans_for($productID, $opts = [], $return_html = false) {
        $Plans = new PipitStripe_Plans();
        $Util = new PipitStripe_Util();
        $plans = $Plans->get_plans_for($productID);


        $default_opts = [
            'template' => 'plans/list.html',
            'customer_email' => 'member',
            'return_url' => SITE_URL . '/subscription/success',
            'cancel_url' => SITE_URL . '/subscription/cancel',
        ];

        $opts = array_merge($default_opts, $opts);

        // data for templating
        $data = [
            'customer_email' => $opts['customer_email'],
            'return_url' => $opts['return_url'],
            'cancel_url' => $opts['cancel_url'],
            'publishable_key' => PIPIT_STRIPE_PUBLISHABLE_KEY
        ];

        // get logged-in member's email address
        if($data['customer_email'] == 'member') {
            if(perch_member_logged_in()) {
                $data['customer_email'] = perch_member_get('email');
            } else {
                $data['customer_email'] = '';
                PerchUtil::debug('Member is not logged in.', 'notice');
            }
        }


        // add $data to each plan for templating
        array_walk($plans, function(&$item) use($data) {
            $item = array_merge($item, $data);
        });

        return $Util->template($default_opts, $opts, $plans, $return_html);
    }







    /**
     * Output the active products for the given API key
     * 
     * @param array $opts
     * @param boolean $return_html
     * 
     */
    function pipit_stripe_products($opts=[], $return_html=false) {
        $Products = new PipitStripe_Products();
        $Util = new PipitStripe_Util();
        $products = $Products->get();
        
        $default_opts = [
            'template' => 'products/list.html',
        ];

        return $Util->template($default_opts, $opts, $products, $return_html);
    }







    /**
     * Output a single product
     * 
     * @param array $opts
     * @param boolean $return_html
     * 
     */
    function pipit_stripe_product($productID, $opts=[], $return_html=false) {
        $Products = new PipitStripe_Products();
        $Util = new PipitStripe_Util();
        $products = $Products->get_product($productID);
        
        $default_opts = [
            'template' => 'products/detail.html',
        ];

        return $Util->template($default_opts, $opts, [$products], $return_html);
    }







    /**
     * Output an unsubscribe Perch Form for a single plan or subscription
     * 
     * @param string $planID
     * @param string $subscriptionID
     * @param array $opts
     * @param boolean $return_html
     * 
     */
    function pipit_stripe_unsubscribe_form($planID = '', $subscriptionID = '', $opts=[], $return_html=false) {
        $Util = new PipitStripe_Util();

        $default_opts = [
            'template' => 'plans/unsubscribe_form.html',
            'return_url' => SITE_URL . '/unsubscribe/success',
            'customer_email' => 'member',
        ];
        
        $opts = array_merge($default_opts, $opts);

        // data for templating
        $data = [
            'planID' => $planID,
            'subscriptionID' => $subscriptionID,
            'customer_email' => $opts['customer_email'],
            'return_url' => $opts['return_url'],
        ];

        // get logged-in member's email address
        if(perch_member_logged_in()) {
            $data['customer_email'] = perch_member_get('email');
        } else {
            PerchUtil::debug('Member is not logged in', 'error');
            return false;
        }
        

        return $Util->template($default_opts, $opts, $data, $return_html);
    }