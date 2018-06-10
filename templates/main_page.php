<?php defined('ABSPATH') || exit; ?>

<div class="wrap">

	<h1><?=__('License Manager', 'lima'); ?></h1>

	<form method="post">
		<?php
			$licenses->prepare_items();
			$licenses->display();
		?>
	</form>

</div>