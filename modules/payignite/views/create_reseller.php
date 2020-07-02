<article class="page">
<!-- This jQuery lib needs to be here, or stripeToken won't work.
     Earlier, locally supplied version probably incompatible.
-->
<script src="https://code.jquery.com/jquery-1.12.3.min.js" integrity="sha256-aaODHAgvwQW1bFOGXMeX+pC4PZIPsvn2h1sArYOhgXQ=" crossorigin="anonymous"></script>

<div id="body" class="section">
    {{ if user:logged_in }}
    <div class="panel panel-default">
        <div class="panel-heading"><h2>{{ template:title }}<a style="float: right" id="backBtn" class="btn btn-default btn" onclick="window.history.go('-1')">
            <span class="glyphicon glyphicon-arrow-left">Back</span></a></h2>
        </div>

<?php
// Pay for and attach customer to plan.
if (isset($_POST['billing_name'])):
    $token = $_POST['stripeToken'];
    $plan_id = $_POST['plan_id'];

    /* Might need this later. For now, we delete the user from Stripe when they cancel the subscription.
    // Get the user from default_users table.
    $query = $this->db->query('
        SELECT id, email
        FROM `switchr`.`default_users`
    ');
    $customer_email = $query->result_array()[0];
    //var_dump($customer_email);die;

    $customer = \Stripe\Customer::retrieve(array(
        'email' => $this->current_user->email, )
    );
    */

    $customer = \Stripe\Customer::create(
        array(
        'source' => $token,
        'plan' => $plan_id,
        'email' => $this->current_user->email, )
    );

    // Add an entry to subscriptions table.
    $entry_data = array(
        //'sub_user_id' => $this->current_user->id,
        'sub_customer_id' => $customer->id,
        'sub_subscription_id' => $customer->subscriptions->data[0]->id,
        'sub_plan_id' => $customer->subscriptions->data[0]->plan->id,
        'sub_interval' => $customer->subscriptions->data[0]->plan->interval_count,
    );

    $this->streams->entries->insert_entry($entry_data, 'subscriptions', 'payignite');

    redirect('/hosts');

// Create/retrieve plan
elseif (isset($_POST['hosts'])):

    $hosts = $_POST['hosts'];
    // $s3 = $_POST['s3'];
    // $ftp = $_POST['ftp'];
    // $local = $_POST['local'];

    $plan_id = "R{$hosts}";
    $plan = $this->Payignite->Plan->createPlan($plan_id);
?>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>

<script type="text/javascript">
    Stripe.setPublishableKey('pk_test_YGcQD9oSkJC72hQF0mnw78rP');
</script>

<div style="padding-left:1em;">
    <div style="display:block;">
        <label for="hosts" class="" style="display:inline;">Hosts:</label>
        <p id="hosts" style="display:inline;"><?php echo $hosts; ?></p>
    </div>
    <!-- <div style="display:block;">
        <label for="s3" class="">S3 Backups:</label>
        <p id="s3" style="display:inline;"><?php echo $s3; ?></p>
    </div>
    <div style="display:block;">
        <label for="ftp" class="">FTP Backups:</label>
        <p id="ftp" style="display:inline;"><?php echo $ftp; ?></p>
    </div>
    <div style="display:block;">
        <label for="local" class="">Local Backups:</label>
        <p id="local" style="display:inline;"><?php echo $local; ?></p>
    </div> -->
    <div style="display:block;">
        <label for="total" class="">Total Amount:</label>
        <p id="total" style="display:inline;">$<?php echo $plan['amount'] / 100; ?></p>
    </div>
</div>

<form action="" method="POST" id="payment-form" class="form-horizontal">
    <span class="payment-errors"></span>

    <div class="form-group">
        <label for="billing-name" class="col-sm-4 control-label">Billing Name</label>
        <div class="col-sm-8">
            <input id="billing-name" type="text" size="20" name="billing_name" class="billing-name form-control" style="width:initial;">
        </div>
    </div>

    <div class="form-group">
        <label for="billing-address-1" class="col-sm-4 control-label">Address</label>
        <div class="col-sm-8">
            <input id="billing-address-1" type="text" size="20" name="billing_address_1" class="billing-address-1 form-control" style="width:initial;">
        </div>
    </div>

    <div class="form-group">
        <label for="billing-address-2" class="col-sm-4 control-label">Suite/Apt #</label>
        <div class="col-sm-8">
            <input id="billing-address-2" type="text" size="20" name="billing_address_2" class="billing-address-2 form-control" style="width:initial;">
        </div>
    </div>

    <div class="form-group">
        <label for="billing-city" class="col-sm-4 control-label">Billing City</label>
        <div class="col-sm-8">
            <input id="billing-city" type="text" size="20" name="billing_city" class="billing-city form-control" style="width:initial;">
        </div>
    </div>

    <div class="form-group">
        <label for="billing-state" class="col-sm-4 control-label">Billing State</label>
        <div class="col-sm-8">
            <input type="text" size="20" name="billing_state" class="billing-state form-control" style="width:initial;">
        </div>
    </div>

    <div class="form-group">
        <label for="billing-zip" class="col-sm-4 control-label">Billing Zip</label>
        <div class="col-sm-8">
            <input type="tel" size="20" name="billing_zip" class="billing-zip form-control" style="width:initial;">
        </div>
    </div>

    <div class="form-group">
        <label for="card-number" class="col-sm-4 control-label">Card Number</label>
        <div class="col-sm-8">
            <input type="tel" size="20" data-stripe="number" class="card-number form-control" style="width:initial;">
        </div>
    </label>
    </div>

    <div class="form-group">
        <label for="card-expiry-month" class="col-sm-4 control-label">Expiration Month (MM)</label>
        <div class="col-sm-8">
            <input id="card-expiry-month" type="tel" size="2" maxlength="2" data-stripe="exp_month" class="card-expiry-month form-control" style="width:initial;">
        </div>
    </div>

    <div class="form-group">
        <label for="card-expiry-year" class="col-sm-4 control-label">Expiration Year (YY)</label>
        <div class="col-sm-8">
            <input id="card-expiry-year" type="tel" size="2" maxlength="2" data-stripe="exp_year" class="card-expiry-year form-control" style="width:initial;">
        </div>
    </div>

    <div class="form-group">
        <label for="cvc" class="col-sm-4 control-label">CVC</label>
        <div class="col-sm-8">
            <input id="cvc" type="tel" size="4" maxlength="4" data-stripe="cvc" class="card-cvc form-control" style="width:initial;">
        </div>
    </div>

    <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">

    <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
            <input type="submit" class="submit btn btn-info" value="Submit Payment" style="width:initial;">
        </div>
    </div>
</form>

<script>
    function stripeResponseHandler(status, response) {
      // Grab the form:
      var $form = $('#payment-form');

      if (response.error) { // Problem!
        // Show the errors on the form:
        $form.find('.payment-errors').text(response.error.message);
        $form.find('.submit').prop('disabled', false); // Re-enable submission

      } else { // Token was created!

        // Get the token ID:
        var token = response.id;
        // need this to get all output for PHP
        //var jsonResponse = JSON.stringify(response);

        // Insert the token ID into the form so it gets submitted to the server:
        $form.append($('<input type="hidden" name="stripeToken">').val(token));
        //$('#token').val(token);
        //$form.append($('<input type="hidden" name="response">').val(jsonResponse));

        // Submit the form:
        $form.get(0).submit();
      }
    }
</script>

<script>
$(function() {
    var $form = $('#payment-form');
    $form.submit(function(event) {
    // Disable the submit button to prevent repeated clicks:
    $form.find('.submit').prop('disabled', true);

    // Request a token from Stripe, automatically grabbing all fields
    //Stripe.card.createToken($form, stripeResponseHandler);

    // Use Stripe.js to validate card info before sending.
    if (Stripe.card.validateCardNumber($('.card-number').val()) &&
        Stripe.card.validateExpiry($('.card-expiry-month').val(), $('.card-expiry-year').val()) &&
        Stripe.card.validateCVC($('.card-cvc').val()))
    {
        // Request a token from Stripe, manually grabbing all fields
        Stripe.card.createToken({
            number: $('.card-number').val(),
            cvc: $('.card-cvc').val(),
            exp_month: $('.card-expiry-month').val(),
            exp_year: $('.card-expiry-year').val(),
            name: $('.billing-name').val(),
            address_line1: $('.billing-address-1').val(),
            address_line2: $('.billing-address-2').val(),
            address_city: $('.billing-city').val(),
            address_state: $('.billing-state').val(),
            address_zip: $('.billing-zip').val()
            //address_country: billing address country
        }, stripeResponseHandler);
    } else {
        $form.find('.payment-errors').text('Check your card number, expiration, and CVC for errors.');
        $form.find('.submit').prop('disabled', false); // Re-enable submission
    }

    // Prevent the form from being submitted:
    return false;
  });
});
</script>

<?php
else:  // Make a Plan
?>

<form action="" method="POST" id="plan-form" class="form-horizontal">
    <div class="form-group">
        <label for="hosts" class="col-sm-4 control-label">Number of Hosts</label>
        <div class="col-sm-8">
            <input id="hosts" type="number" size="5" name="hosts" class="amount form-control" value=0 min=0 required style="width:initial;">
        </div>
    </div>

    <!-- <div class="form-group">
        <label for="s3" class="col-sm-4 control-label">Amount of Amazon S3 Backups</label>
        <div class="col-sm-8">
            <input id="s3" type="number" size="5" name="s3" class="amount form-control" value=0 min=0 required style="width:initial;">
        </div>
    </div>

    <div class="form-group">
        <label for="ftp" class="col-sm-4 control-label">Amount of FTP Backups</label>
        <div class="col-sm-8">
            <input id="ftp" type="number" size="5" name="ftp" class="amount form-control" value=0 min=0 required style="width:initial;">
        </div>
    </div>

    <div class="form-group">
        <label for="local" class="col-sm-4 control-label">Amount of Local Backups</label>
        <div class="col-sm-8">
            <input id="local" type="number" size="5" name="local" class="amount form-control" value=0 min=0 required style="width:initial;">
        </div>
    </div> -->

    <div class="form-group">
        <!-- <input type="hidden" name="total"> -->
        <label for="total" class="col-sm-4 control-label">Total Amount: </label>
        <div class="col-sm-8" style="padding-top:7px;">
            $<span id="total" class="">0</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-4 col-sm-8">
            <input type="submit" class="submit btn btn-info" value="Pay for Plan" style="width:initial;">
        </div>
    </div>
</form>

<script>
$('.amount').on('click keyup change', function() {
    var hosts = $('#hosts').val() * 5;
    // var s3 = $('#s3').val() * 5;
    // var ftp = $('#ftp').val() * 5;
    // var local = $('#local').val() * 5;

    var total = hosts;
    $('#total').text(total);
    // $('input[name="total"]').val(total);
});
</script>

<?php
endif;
?>
{{ else }}
<p><a href="users/login">Please login first.</a></p>
{{ endif }}
</div>
</div>

</article>
