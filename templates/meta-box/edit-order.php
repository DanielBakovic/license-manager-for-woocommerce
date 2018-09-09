<p>This order has the following license keys attached to it</p>

<?php foreach ($licenses as $license): ?>
	<div>
		<span class="dashicons dashicons-admin-network"></span>
		<code><?=$this->crypto->decrypt($license->license_key);?></code>
	</div>
<?php endforeach; ?>

<p>The customer has NOT yet received these keys per email. you can <u>send them now</u> or let the plugin do this automatically for you.</p>