var stripe = Stripe('publishable_key', {
    betas: ['checkout_beta_4']
});

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
