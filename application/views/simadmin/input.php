<?php 
	$o_bp = array(
			'name'=>'loc',
			'id'=> 'bp',
			'placeholder'=>'Bus latest location',
			'class'=>'input'
		);

	$o_bus_id = array(
			'name'=>'bus_id',
			'id'=> 'bus_id',
			'placeholder'=>'Bus ID',
			'class'=>'input'
		);

	$o_last_stop = array(
			'name'=>'last_stop',
			'id'=> 'last_stop',
			'placeholder'=>'Last stop',
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
		Simulator | Admin Input page
	</title>
</head>
<body>
<?php echo form_open('simadmin/put_bus_location') ?>
<?php echo form_input($o_bus_id) ?>
<br>
<?php echo form_input($o_bp) ?>
<br>
<?php echo form_input($o_last_stop) ?>
<br>
<br>
<?php echo form_submit($o_submit); ?>
<?php echo form_close() ?>
</body>
</html>