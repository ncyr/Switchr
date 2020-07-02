<!--   order.php view -->
<?php
	$currentCity = $this->session->userdata('current_city');
	if ( !isset($currentCity) )
	{
		$currentCity = 0;
	}
?>
<div id="outside-wrapper">
    <div id="membership-wrapper">
		<div id="change_location" style="float:right">
			<span class="small-notice">More cities coming soon!</span>
			<?php echo form_open( $this->module.'/change_location'); ?>
			<select name="city_id">
                            <?php if ($currentCity == 0) {
                            echo '<option disabled selected>' . form_label( lang('payignite:change_city'), 'gym_city' ) . '</option>';
                            } ?>
			{{ payignite:city }}
			<option value="{{ id }}" {{ if id == <?php echo $currentCity?> }} <?php if ($currentCity != 0) { echo "selected"; } ?> >{{ city_name }}
			{{ else }}
				>{{ city_name }}
			{{ endif }}
				</option>
			{{ /payignite:city }}
			</select>&nbsp;
			<?php echo form_submit( 'citySubmitBtn', 'GO' ); echo form_close(); ?>
		</div>
		<div class="cboth"></div>
		<span class="small-notice">You must pick at least 2 gyms.</span></br>
		<div class="floatLeft">
			<form action="payignite/order" id="order-form" method="post">
						<select name="gym_name[]" class="gym_drop">
							<?php if (!$currentCity) { echo '<option disabled selected>->Select a City</option>'; } ?>
							{{ streams:cycle stream="gyms" namespace="payignite" where="`gym_city`='<?php echo $this->session->userdata('current_city')?>'" }}
							<option value="{{ id }}">{{ gym_name }}</option>
							{{ /streams:cycle }}
						</select>
		</div><div class="floatLeft">
						<select type="dropdown" name="gym_visits[]" class="visit_drop">
							<?php foreach(range(1, 31) as $row):?>
							<option value="<?php echo $row ?>"><?php echo $row ?></option>
							<?php endforeach; ?>
						</select>
		</div><div class="cboth"></div><div class="floatLeft">
						<select name="gym_name[]" class="gym_drop">
							<?php if (!$currentCity) { echo '<option disabled selected>->Select a City</option>'; } ?>
							{{ streams:cycle stream="gyms" namespace="payignite" where="`gym_city`='<?php echo $this->session->userdata('current_city')?>'" }}
							<option value="{{ id }}">{{ gym_name }}</option>
							{{ /streams:cycle }}
						</select>
		</div><div class="floatLeft">
						<select type="dropdown" name="gym_visits[]" class="visit_drop">
							<?php foreach(range(1, 31) as $row):?>
							<option value="<?php echo $row ?>"><?php echo $row ?></option>
							<?php endforeach; ?>
						</select>
		</div><div class="cboth"></div><div class="floatLeft">
						<select name="gym_name[]" class="gym_drop">
							<option disabled selected>->Select Another Gym</option>
							{{ streams:cycle stream="gyms" namespace="payignite" where="`gym_city`='<?php echo $this->session->userdata('current_city')?>'" }}
							<option value="{{ id }}">{{ gym_name }}</option>
							{{ /streams:cycle }}
						</select>
		</div><div class="floatLeft">
						<select type="dropdown" name="gym_visits[]" class="visit_drop">
							<?php foreach(range(1, 31) as $row):?>
							<option value="<?php echo $row ?>"><?php echo $row ?></option>
							<?php endforeach; ?>
						</select>
		</div><div class="cboth"></div><div class="floatLeft">
						<select name="gym_name[]" class="gym_drop">
							<option disabled selected>->Select Another Gym</option>
							{{ streams:cycle stream="gyms" namespace="payignite" where="`gym_city`='<?php echo $this->session->userdata('current_city')?>'" }}
							<option value="{{ id }}">{{ gym_name }}</option>
							{{ /streams:cycle }}
						</select>
		</div><div class="floatLeft">
						<select type="dropdown" name="gym_visits[]" class="visit_drop">
							<?php foreach(range(1, 31) as $row):?>
							<option value="<?php echo $row ?>"><?php echo $row ?></option>
							<?php endforeach; ?>
						</select>

		</div><div class="cboth"></div><div class="floatLeft">
						<select name="gym_name[]" class="gym_drop">
							<option disabled selected>->Select Another Gym</option>
							{{ streams:cycle stream="gyms" namespace="payignite" where="`gym_city`='<?php echo $this->session->userdata('current_city')?>'" }}
							<option value="{{ id }}">{{ gym_name }}</option>
							{{ /streams:cycle }}
						</select>
		</div><div class="floatLeft">
					<div>
						<select type="dropdown" name="gym_visits[]" class="visit_drop">
							<?php foreach(range(1, 31) as $row):?>
							<option value="<?php echo $row ?>"><?php echo $row ?></option>
							<?php endforeach; ?>
						</select>
					</div>
		</div>
		<div class="cboth"></div>
			<div id="refreshOrder"><br/><a href="#" id="addGymBtn" title="Add Gym" class="gymBtn tooltip btn orange">Add Gym</a> &nbsp;<a href="<?php echo $this->module ?>/order" id="clearGymBtn" title="Clear Gyms" class="tooltip btn red">Clear</a> &nbsp;<a id="refreshGymBtn" title="Refresh Order Total" class="tooltip btn blue refresh">Refresh Total</a></div>
			<br/>
			<div id="commitmentDuration">
			<?php echo form_label('Commitment Duration: ', 'discount') ?>&nbsp;<?php echo form_dropdown('discount', array('MONTHLY' => 'Monthly', '3MONTHS' => '3 Months','6MONTHS' => '6 Months','12MONTHS' => '12 Months',)) ?>
			<br/><br/><input id="agree" name="agree" type="checkbox" value="1">&nbsp;<a id="agreement" href=""><?php echo form_label('I have read the agreement terms. (click here)', 'agree') ?></a>
			</div>	
			<br>
			<div id="orderTotalWrapper">Total Charged Monthly: <span id="currency">$</span><span id="orderTotal"></span></div>
			<div style="clear:left"></div>
			<hr><br/>
			<div style="float:left; margin-right:100px;">
				<?php echo form_label('Name on Card', 'card_name') ?>
				<br/>
				<?php
					$data = array(
						'name'        => 'card_name',
						'id'          => 'card_name',
						'maxlength'   => '100',
						'style'       => 'width:155px',
		
					);
					echo form_input($data);
				?><br/>
				<?php echo form_label('Street Address', 'card_add1') ?>
				<br/>
				<?php
					$data = array(
						'name'        => 'card_add1',
						'id'          => 'card_add1',
						'maxlength'   => '100',
						'style'       => 'width:155px',
		
					);
					echo form_input($data);
				?>
				<br/>
				<?php echo form_label('Suite/Apt#', 'card_add2') ?>
				<br/>
				<?php
					$data = array(
						'name'        => 'card_add2',
						'id'          => 'card_add2',
						'maxlength'   => '100',
						'style'       => 'width:155px',
		
					);
					echo form_input($data);
				?>
				<br/>
				<?php echo form_label('City', 'card_city') ?>
				<br/>
				<?php
					$data = array(
						'name'        => 'card_city',
						'id'          => 'card_city',
						'maxlength'   => '100',
						'style'       => 'width:155px',
		
					);
					echo form_input($data);
				?>
				<br/>
				<?php echo form_label('State', 'card_state') ?>
				<br/>
				<?php
					$data = array(
									'AL'=>"Alabama",
									'AK'=>"Alaska", 
									'AZ'=>"Arizona", 
									'AR'=>"Arkansas", 
									'CA'=>"California", 
									'CO'=>"Colorado", 
									'CT'=>"Connecticut", 
									'DE'=>"Delaware", 
									'DC'=>"District Of Columbia", 
									'FL'=>"Florida", 
									'GA'=>"Georgia", 
									'HI'=>"Hawaii", 
									'ID'=>"Idaho", 
									'IL'=>"Illinois", 
									'IN'=>"Indiana", 
									'IA'=>"Iowa", 
									'KS'=>"Kansas", 
									'KY'=>"Kentucky", 
									'LA'=>"Louisiana", 
									'ME'=>"Maine", 
									'MD'=>"Maryland", 
									'MA'=>"Massachusetts", 
									'MI'=>"Michigan", 
									'MN'=>"Minnesota", 
									'MS'=>"Mississippi", 
									'MO'=>"Missouri", 
									'MT'=>"Montana",
									'NE'=>"Nebraska",
									'NV'=>"Nevada",
									'NH'=>"New Hampshire",
									'NJ'=>"New Jersey",
									'NM'=>"New Mexico",
									'NY'=>"New York",
									'NC'=>"North Carolina",
									'ND'=>"North Dakota",
									'OH'=>"Ohio", 
									'OK'=>"Oklahoma", 
									'OR'=>"Oregon", 
									'PA'=>"Pennsylvania", 
									'RI'=>"Rhode Island", 
									'SC'=>"South Carolina", 
									'SD'=>"South Dakota",
									'TN'=>"Tennessee", 
									'TX'=>"Texas", 
									'UT'=>"Utah", 
									'VT'=>"Vermont", 
									'VA'=>"Virginia", 
									'WA'=>"Washington", 
									'WV'=>"West Virginia", 
									'WI'=>"Wisconsin", 
									'WY'=>"Wyoming");
		
					echo form_dropdown('card_state', $data);
				?>
				<br/>
				<?php echo form_label('Zipcode', 'card_zip') ?>
				<br/>
				<?php
					$data = array(
						'name'        => 'card_zip',
						'id'          => 'card_zip',
						'maxlength'   => '9',
						'style'       => 'width:75px',
		
					);
					echo form_input($data);
				?>
				</div>
				<br/>
				<div>
				<?php echo form_label('Credit Card #:', 'card_number') ?>
				<br/>
				<?php
					$data = array(
						'name'        => 'card_number',
						'id'          => 'card_number',
						'maxlength'   => '19',
						'style'       => 'width:155px',
		
					);
					echo form_input($data);
				?><br/>
				<?php echo form_label('Expiration: ', 'card_exp_month') ?>
				<br/>
				<?php
					$range = array_combine(range(1, 12), range(1, 12));
					echo form_dropdown('card_exp_month', $range);
					
					$range = array_combine(range( date('Y'), (date('Y')+10)), range( date('Y'), (date('Y')+10)));
					echo form_dropdown('card_exp_year', $range);
				?><br/>
				<?php echo form_label('Security Code: ', 'card_cvc') ?>
				<br/>
				<?php
					$data = array(
						'name'        => 'card_cvc',
						'id'          => 'card_cvc',
						'maxlength'   => '4',
						'style'       => 'width:35px',
		
					);
					echo form_input($data);
				?><br/>
				</div>
				<div style="clear:left"></div>
				<br/>
				<div>
					 <?php
					$data = array(
						  'name'        => 'orderBtn',
						  'value'       => 'Order',
						  'class'       => 'btn green',
						);
					echo form_submit($data);
				?>
				<?php echo form_reset('resetBtn', 'Reset') ?>
				<?php echo form_close() ?>
			</div>
		</div>
	</div>
</div>
<div class="dialog" style="display:none" title="Notice"><h3>Processing Your Order...</h3><p>We're processing your order now. Please wait.</p></div>
<div class="cboth">
<script>
	$('#addGymBtn').click(function(e){
		e.preventDefault();
		var clone = $('tr.gym-row').first().clone();
                // adds 'on change' (price check) to dynamiclly created gym rows
                clone.on('change', function(e){
                    e.preventDefault();
                    $.ajax({
			url: '<?php echo $this->module?>/order/getTotalPrice',
			data: $('form').serialize(),
			type: "post",
				success: function(data){
					$('#orderTotal').html(data);
				}
			});
                    });
		clone.appendTo('tbody').find('tr.gym-row').last();
	});
    $('#removeGymBtn').on('click',function(e){
		e.preventDefault();
		$(this).closest('tr').remove();
	});
	
	$("#order-form").validate({
		rules: {
			card_name: "required",
			card_add1: "required",
			card_city: "required",
			card_state: "required",
			card_zip: "required",
			card_number: "required",
			card_exp_month: "required",
			card_exp_year: "required",
			card_cvc: "required",
			agree: "required",
			
		},
		messages: {
			agree: "Please accept our policy<br/>"
		},
		submitHandler: function(form){
			$("[name=orderBtn]").attr('disabled', 'true');
			$(".dialog").dialog({
					closeOnEscape: false,
					beforeclose: function (event, ui) { return false; },
					dialogClass: "noclose"
			});
			$('.dialog p').html('Processing your card now, please wait.');
			$.ajax({
				url: '<?php echo $this->module?>/order/ajax_order',
				data: $('form').serialize(),
				type: "post",
				success: function(data){
					if(data == false)
					{
						$('.dialog p').html('Your order has been DECLINED. <br/>Please try another card, or try again.');
						$('#orderTotalWrapper').append('<span class="error" style="font-size: 10px"><br>Your order has been DECLINED. <br/>Please try another card, or try again.</span>');
						$("[name=orderBtn]").removeAttr('disabled');
						sleep(2000, $('.dialog').dialog('close'));
					}
					if (data == true) {
						$('.dialog p').html('Your order has been APPROVED. <br/>Please wait while we take you to the membership area.');
						sleep(2000, goto('<?php echo $this->module?>'));
					}
				},
			});
		}
	});
	function sleep(millis, callback) {
    setTimeout(function()
            { callback(); }
    , millis);
	}
	function goto(location){
		window.location.href = location;
	}
	//price check
	$('.gym_drop, .visit_drop, [name=discount]').on('change', function(e){
		e.preventDefault();
		$.ajax({
			url: '<?php echo $this->module?>/order/getTotalPrice',
			data: $('form').serialize(),
			type: "post",
				success: function(data){
					$('#orderTotal').html(data);
				}
			});
		});
	$('.refresh').on('click', function(e){
		e.preventDefault();
		$(this).html('Getting Total...');
			$.ajax({
				url: '<?php echo $this->module?>/order/getTotalPrice',
				data: $('form').serialize(),
				type: "post",
					success: function(data){
						$('#orderTotal').html(data);
						$('.refresh').html('Refresh Total');
					}
			});
		});
	</script>