## Instalation
* Download the latest version of the Stripe App.
* Unzip the download
* Place the `pipit_stripe` folder in `perch/addons/apps`
* Add `pipit_stripe` to your `perch/config/apps.php`

### Requirements
* Perch or Perch Runway 3+
* PHP 7+
* Perch Members app

### Recommended
* Pipit Members app
* Pipit Emails app

## Configuration

Add API keys and webhook endpoint secret to your Perch config file `/perch/config/config.php`:

```php
define('PIPIT_STRIPE_PUBLISHABLE_KEY', '');
define('PIPIT_STRIPE_SECRET_KEY', '');
define('PIPIT_STRIPE_ENDPOINT_SECRET', '');
define('SITE_URL', '');
```

| Setting                      | Value                                       |
|------------------------------|---------------------------------------------|
| PIPIT_STRIPE_PUBLISHABLE_KEY | Stripe publishable API key                  |
| PIPIT_STRIPE_SECRET_KEY      | Stripe secret API key                       |
| PIPIT_STRIPE_ENDPOINT_SECRET | Stripe endpoint secret                      |
| SITE_URL                     | Your site domain e.g. `https://example.com` |



### Stripe webhook events
You can configure your webhook endpoint and what events you want Stripe to send to the endpoint via the Stripe Dashboard. Refer to the Stripe documentation on how to [configure a webhook endpoint](https://stripe.com/docs/webhooks/configure). When configuring the endpoint make sure you select all the Stripe events your site/application needs to receive.

If you are using Perch Runway, you can add your endpoint to `perch/templates/api`. If your endpoint template is `perch/templates/api/stripe/webhook.php`, your endpoint URL becomes `https://example.com/api/stripe/webhook`.

Note that the endpoint must be a publicly accessible URL. If you would like to test the webhook endpoint from a local environment, you can use a service like [ngrok](https://ngrok.com/) to make your local web server publicly accessible. Otherwise, you can test on a staging environment.


### Scheduled Tasks
The app comes with scheduled tasks to fetch Stripe products and their plans every hour. The app uses Perch's centralised scheduler. To learn more about how to set up a cron job to run the Perch scheduler, refer to the [Perch docs](https://docs.grabaperch.com/runway/getting-started/installing/scheduled-tasks/).


### Products and Plans cache
The Stripe products and their plans are fetched from the Stripe API and stored in `perch/pipit_stripe`. This is outside of the app folder so you'd be able to update the app in the future without deleting the cache.

If the folder `perch/pipit_stripe` does not exist, the app will create it given PHP write permissions allows the app to do so.

The app does not protect this folder by default. So if you don't want the cache files to be publicly accessible, you can deny access to all requests to this folder by creating `perch/pipit_stripe/.htaccess`:

```
Deny from all
```



## Functions

### Plans Functions

You can use these functions to output the plans details as well as the subscribe button for each plan. Note the buttons require Javascript to work. See [Javascript](#javascript)

#### pipit_stripe_plans()
Get or output all plans.

```php
pipit_stripe_plans($opts, $return_html);
```

**Parameters**

| Name         | Type    | Description                                                                                                             |
|--------------|---------|-------------------------------------------------------------------------------------------------------------------------|
| $opts        | array   | Options array. See table below.                                                                                         |
| $return_html | boolean | Set to `true` to have the rendered HTML returned instead of echoed. This is ignored if `$opts['skip-template'] = true`. |


**Options**

| Option         | Value                                                                                                          | Default                              |
|----------------|----------------------------------------------------------------------------------------------------------------|--------------------------------------|
| template       | The name of a template to use to display the content.                                                          | `plans/list.html`                    |
| skip-template  | True or false. Bypass template processing and return the content in an associative array.                      | `false`                              |
| return-html    | True or false. For use with  `skip-template`. Adds the HTML onto the end of the returned array with key  html. | `false`                              |
| return_url     | The URL to which Stripe redirects upon a successful subscription.                                              | `SITE_URL . '/subscription/success'` |
| cancel_url     | The URL to which Stripe redirects upon a failed or cancelled subscription.                                     | `SITE_URL . '/subscription/cancel'`  |
| customer_email | The customer email. Use `member` and the app will use the logged-in member's email address.                    | `member`                             |


```php
pipit_stripe_plans([
    'template' => 'plans/list.html',
    'return_url' => SITE_URL . '/subscription/success',
    'cancel_url' => SITE_URL . '/subscription/error',
    'customer_email' => 'member',
]);
```

```php
$plans = pipit_stripe_plans(['skip-template' => true]);
echo '<pre>' . print_r($plans, 1) . '</pre>';
```



#### pipit_stripe_plan()
Get or output a single plan.

```php
pipit_stripe_plan($planID, $opts, $return_html);
```

| Name         | Type    | Description                                                                                                             |
|--------------|---------|-------------------------------------------------------------------------------------------------------------------------|
| $planID      | string  | Plan ID e.g. `plan_xxxxxxxxxxx`                                                                                         |
| $opts        | array   | Options array.                                                                                                          |
| $return_html | boolean | Set to `true` to have the rendered HTML returned instead of echoed. This is ignored if `$opts['skip-template'] = true`. |


```php
pipit_stripe_plan('plan_xxxxxxxxxxx', [
    'template' => 'plans/detail.html',
    'return_url' => SITE_URL . '/subscription/success',
    'cancel_url' => SITE_URL . '/subscription/error',
    'customer_email' => 'member',
]);
```



#### pipit_stripe_plans_for()
Get or output plans for a single product.

```php
pipit_stripe_plans_for($productID, $opts, $return_html);
```

| Name         | Type    | Description                                                                                                             |
|--------------|---------|-------------------------------------------------------------------------------------------------------------------------|
| $productID   | string  | Plan ID e.g. `prod_xxxxxxxxxxx`                                                                                         |
| $opts        | array   | Options array.                                                                                                          |
| $return_html | boolean | Set to `true` to have the rendered HTML returned instead of echoed. This is ignored if `$opts['skip-template'] = true`. |


```php
pipit_stripe_plans_for('prod_xxxxxxxxxxx', [
    'template' => 'plans/list.html',
    'return_url' => SITE_URL . '/subscription/success',
    'cancel_url' => SITE_URL . '/subscription/error',
    'customer_email' => 'member',
]);
```


---

### Product Functions
#### pipit_stripe_products()
Get or output all products.


```php
pipit_stripe_products($opts, $return_html);
```

**Parameters**

| Name         | Type    | Description                                                                                                             |
|--------------|---------|-------------------------------------------------------------------------------------------------------------------------|
| $opts        | array   | Options array. See table below.                                                                                         |
| $return_html | boolean | Set to `true` to have the rendered HTML returned instead of echoed. This is ignored if `$opts['skip-template'] = true`. |


**Options**

| Option         | Value                                                                                                          | Default                              |
|----------------|----------------------------------------------------------------------------------------------------------------|--------------------------------------|
| template       | The name of a template to use to display the content.                                                          | `products/list.html`                 |
| skip-template  | True or false. Bypass template processing and return the content in an associative array.                      | `false`                              |
| return-html    | True or false. For use with  `skip-template`. Adds the HTML onto the end of the returned array with key  html. | `false`                              |


```php
pipit_stripe_products([
    'template' => 'products/list.html',
]);
```

```php
$plans = pipit_stripe_products(['skip-template' => true]);
echo '<pre>' . print_r($plans, 1) . '</pre>';
```


#### pipit_stripe_product()
Get or output a single product.

```php
pipit_stripe_product($productID, $opts, $return_html);
```

| Name         | Type    | Description                                                                                                             |
|--------------|---------|-------------------------------------------------------------------------------------------------------------------------|
| $productID   | string  | Plan ID e.g. `prod_xxxxxxxxxxx`                                                                                         |
| $opts        | array   | Options array.                                                                                                          |
| $return_html | boolean | Set to `true` to have the rendered HTML returned instead of echoed. This is ignored if `$opts['skip-template'] = true`. |


```php
pipit_stripe_product('prod_xxxxxxxxxxx', [
    'template' => 'products/detail.html',
]);
```

---

### Customer Functions

The customers (and their subscriptions) data is not cached like the products and plans. Unlike the Products and Plans functions, the customer functions return Stripe objects (or `false`).

#### pipit_stripe_get_customers()
Retrieve customers from Stripe API.

```php
$customers = pipit_stripe_get_customers($opts);
```

`$opts` is an option array. Refer to Stripe documentation for available options.


#### pipit_stripe_get_customer()
Retrieve a single customer from Stripe API.

```php
$customer = pipit_stripe_get_customer($customerID);
```



#### pipit_stripe_get_customer_subscriptions()
Retrieve a single customer's subscriptions from Stripe API.

```php
$subscriptions = pipit_stripe_get_customer_subscriptions($email);
```

For a logged-in member (using the Perch Members app):

```php
$email = perch_member_get('email');
$subscriptions = pipit_stripe_get_customer_subscriptions($email);
```

---

---

### Subscription Functions



#### pipit_stripe_unsubscribe_form()
Outputs a subscription cancellation form. The user must be a logged-in member, otherwise the form will not attempt to unsubscribe anyone.

```php
pipit_stripe_unsubscribe_form($planID, $subscriptionID, $opts, $return_html);
```

| Name              | Type    | Description                                                                                                             |
|-------------------|---------|-------------------------------------------------------------------------------------------------------------------------|
| $planID           | string  | Plan ID e.g. `plan_xxxxxxxxxxx`                                                                                         |
| $subscriptionID   | string  | subscription ID                                                                                                         |
| $opts             | array   | Options array.                                                                                                          |
| $return_html      | boolean | Set to `true` to have the rendered HTML returned instead of echoed. This is ignored if `$opts['skip-template'] = true`. |

**Options**

| Option         | Value                                                                                                          | Default                              |
|----------------|----------------------------------------------------------------------------------------------------------------|--------------------------------------|
| template       | The name of a template to use to display the content.                                                          | `plans/unsubscribe_form.html`        |
| skip-template  | True or false. Bypass template processing and return the content in an associative array.                      | `false`                              |
| return-html    | True or false. For use with `skip-template`. Adds the HTML onto the end of the returned array with key  html.  | `false`                              |
| return_url     | The URL to which the app redirects upon a successful cancellation.                                             | `/unsubscribe/success`               |


You need to provide the plan ID or the subscription ID. You don't have to provide both. In case you have access to both, providing the subscription ID is preferred.

How to get the plan ID or the subscription ID ultimately depends on your use-case. If your project only has one plan and a customer can only be subscribed to a single plan at a time, then adding the subscription ID as a Perch Member tag (when Stripe fires an event to your webhook endpoint) can be a good solution. However, if your use-case is more complex, you may need to use some of the customer functions above to fetch the customer's subscriptions dynamically.



#### pipit_stripe_cancel_subscription()
You can use this function if you know the subscription ID and want to programmatically cancel it instead of using `pipit_stripe_unsubscribe_form()`.

```php
pipit_stripe_cancel_subscription($subscriptionID, $cancel_at_end);
```

| Name              | Type    | Description                                                                                                             |
|-------------------|---------|-------------------------------------------------------------------------------------------------------------------------|
| $planID           | string  | Plan ID e.g. `plan_xxxxxxxxxxx`                                                                                         |
| $cancel_at_end    | boolean | Whether to cancel the subscription immediately or at the end of it. Default `true`                                      |



#### pipit_stripe_cancel_customer_plan()
You can use this function if you know the plan ID and the customer email, and want to programmatically cancel the subscription instead of using `pipit_stripe_unsubscribe_form()`.

```php
pipit_stripe_cancel_customer_plan($planID, $cancel_at_end, $email);
```

| Name              | Type    | Description                                                                                                             |
|-------------------|---------|-------------------------------------------------------------------------------------------------------------------------|
| $subscriptionID   | string  | subscription ID                                                                                                         |
| $cancel_at_end    | boolean | Whether to cancel the subscription immediately or at the end of it. Default `true`                                      |
| $email            | string  | The customer email. Use `member` and the app will use the logged-in member's email address. Default `member`.           |



#### pipit_stripe_update_subscription()
Programmatically update a subscription.

```php
pipit_stripe_update_subscription($subscriptionID, $opts);
```

| Name              | Type    | Description                                                                                                             |
|-------------------|---------|-------------------------------------------------------------------------------------------------------------------------|
| $subscriptionID   | string  | subscription ID                                                                                                         |
| $opts             | array   | Options array. Refer to Stripe documentation.                                                                           |



```php
// get a customer's subscriptions
$email = perch_member_get('email');
$subs = pipit_stripe_get_customer_subscriptions($email);


// find the subscription you want to upgrade/downgrade
foreach($subs as $Subscription) {
    // $Subscription is a Stripe object

    if(/* your condition */) {
        // Plan ID you want to upgrade/downgrade to
        $planID = 'plan_xxxx';

        // update the subscription
        pipit_stripe_update_subscription($Subscription->id, [
            'cancel_at_period_end' => false,
            'items' => [
                [
                    'id' => $Subscription->items->data[0]->id,
                    'plan' => $planID,
                ]
            ]
        ]);
    }
}
```


Helpful resources:
- [Update a subscription API reference](https://stripe.com/docs/api/subscriptions/update)
- [Upgrading and Downgrading Plans](https://stripe.com/docs/billing/subscriptions/upgrading-downgrading)
- [Canceling and Pausing Subscriptions](https://stripe.com/docs/billing/subscriptions/canceling-pausing)


---

### Webhook functions
#### pipit_stripe_webhook_event()
The function verifies the webhook signature and fires a Perch event. To be used in the webhook endpoint that listens to Stripe events.

The function sends a 400 HTTP response code if the payload or webhook signature is invalid.

```php
// JSON payload
$payload = @file_get_contents('php://input');

// get Stripe event and verify signature
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = pipit_stripe_webhook_event($payload, $sig_header);

// handle events
switch($event->type) {
    case 'invoice.payment_succeeded':
        break;
        
    case 'invoice.payment_failed':
        break;
    
    case 'customer.subscription.deleted':
        break;
}

// Return a response to acknowledge receipt of the event
http_response_code(200);
```






## Templates

Copy the default templates from `pipit_stripe/templates` (minus the the `stripe/admin` folder) and place it in `perch/templates`.

The app uses the `perch:stripe` namespace.


## Javascript
On the pages you output the subscribe buttons, you need to include [Stripe.js](https://stripe.com/docs/stripe-js/reference):

```html
<script src="https://js.stripe.com/v3/"></script>
```

You also need to [create an instance of the Stripe object](https://stripe.com/docs/stripe-js/reference#stripe-function):

```javascript
var stripe = Stripe('your_publishable_key');
```

You could use your PHP constant here:

```php
<script>
    var stripe = Stripe('<?= PIPIT_STRIPE_PUBLISHABLE_KEY ?>');
</script>
```

To use the [SCA-ready Stripe Checkout](https://stripe.com/docs/payments/checkout/migration-from-beta), you may need to specify which beta version you're using:

```php
<script>
    var stripe = Stripe('<?= PIPIT_STRIPE_PUBLISHABLE_KEY ?>', [
        betas: ['checkout_beta_4']
    ]);
</script>
```


### Subscribe buttons
The subscribe buttons can be output with the app's plans functions. You can output a `<button>` with data attributes to handle things dyanmically:

```html
<button data-stripe-btn role="link" data-stripe-plan="<perch:stripe id="id">" data-stripe-qty="1"
        data-stripe-return-url="<perch:stripe id="return_url">"
        data-stripe-cancel-url="<perch:stripe id="cancel_url">"
        data-stripe-customer-email="<perch:stripe id="customer_email">" >
            
    Subscribe

</button>

<div id="error-message" class="error"></div>
```

```javascript
var stripeBtns = document.querySelectorAll('[data-stripe-btn]');
stripeBtns.forEach(function(element) {
    
    element.addEventListener('click', function(){
        // When the customer clicks on the button, redirect
        // them to Checkout.
        stripe.redirectToCheckout({
            items: [{plan: element.dataset.stripePlan, quantity: parseInt(element.dataset.stripeQty)}],

            successUrl: element.dataset.stripeReturnUrl,
            cancelUrl: element.dataset.stripeCancelUrl,
            customerEmail: element.dataset.stripeCustomerEmail,
        })
        .then(function (result) {
            if (result.error) {
                // If `redirectToCheckout` fails due to a browser or network
                // error, display the localized error message to your customer.
                var displayError = document.getElementById('error-message');
                displayError.textContent = result.error.message;
            }
        });
    });
});
```