<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class='wrap postbox table-box table-box-main' id="filter_settings_tab" style='padding:5px 20px;'>
	<h3>
		<?php _e( 'Settings', 'eh_bulk_edit' ); ?>
	</h3>
	<hr>
	<table class='eh-content-table' id='data_table'>
		<tr>
			<td class='eh-content-table-left'>
				<?php _e( "Update custom meta <span style='vertical-align: super;color:green;font-size:12px'>Premium</span> ", 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e( 'Enter custom meta keys seperated by comma.', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				
				<textarea style="width: 65%;" rows="4" id="update_meta_values" disabled="disabled"></textarea>
			</td>
		</tr>
	</table>
	<button id='save_filter_setting_fields' value='save_filter_setting_fields' style='margin:5px 2px 2px 90%;width: 10%; ' class='button button-primary button-large' disabled="disabled"><?php _e( 'Save', 'eh_bulk_edit' ); ?></button>
</div>
