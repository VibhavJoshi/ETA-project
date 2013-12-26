<?php 
	$o_bp = array(
			'name'=>'bp',
			'id'=> 'bp',
			'placeholder'=>'Boarding point',
			'class'=>'input'
		);
	$o_dp = array(
			'name'=>'dp',
			'id'=> 'dp',
			'placeholder'=>'Dropping point',
			'class'=>'input'
		);
	$o_time_start = array(
			'name'=>'time_start',
			'id'=> 'time_s',
			'placeholder'=>'Start time(HH:MM)',
			'class'=>'input'
		);
	$o_time_end = array(
			'name'=>'time_end',
			'id'=> 'time_e',
			'placeholder'=>'End Time(HH:MM)',
			'class'=>'input'
		);

	$o_submit = array(
			'name'=>'formsubmit',
			'value'=>'Submit'
		);
 ?>

<!DOCTYPE html>
<html>
<head>
	<title>
		Simulator | User Input page
	</title>
	<script type="text/javascript" src="<?php echo site_url() ?>js/jquery-1.9.1.js"></script>
	<script type="text/javascript" src="<?php echo site_url() ?>js/jquery-ui-1.10.3.custom.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo site_url() ?>css/flick/jquery-ui-1.10.3.custom.min.css">
</head>
<body>
<?php echo form_open('sim/user_input_b') ?>
<?php echo form_input($o_bp) ?>
<br>
<?php echo form_input($o_dp) ?>
<br>
<?php echo form_submit($o_submit); ?>
<?php echo form_close() ?>

<script type="text/javascript">
	$(function(){
		$('#bp').autocomplete({
			source: '<?php echo site_url() ?>sim/stops_array_bp',
			minLength: 1
		});

		$('#dp').autocomplete({
			source: '<?php echo site_url() ?>sim/stops_array_bp',
			minLength: 1
		});

	});

</script>
</body>
</html>