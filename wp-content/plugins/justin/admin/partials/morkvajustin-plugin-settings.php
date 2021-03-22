<?php
require("functions.php");
mnp_display_nav(); ?>
<div class="container">
	<div class="row">
		<h1>Доставка Justin</h1>
		<?php settings_errors(); ?>
		<hr>
    <div class="settingsgrid">
			<div class="w70">
				<div class="tab-content">
					<div id="tab-1" class="tab-pane active">

					<hr>

						<form method="post" action="options.php">
							<?php
								settings_fields( 'morkvajustin_options_group' );
								do_settings_sections( 'morkvajustin_plugin' );
								submit_button();
							?>
						</form>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		  <?php require 'card.php' ; ?>
		</div>
	</div>
</div>
