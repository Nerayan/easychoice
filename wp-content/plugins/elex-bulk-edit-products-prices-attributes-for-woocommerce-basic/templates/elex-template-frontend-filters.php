<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
$cat_args        = array(
	'hide_empty' => false,
	'order'      => 'ASC',
);
$attributes      = wc_get_attribute_taxonomies();
$attribute_value = get_terms( 'pa_size', $cat_args );
?>

<div class="loader"></div>

<div class='wrap postbox table-box table-box-main' id="top_filter_tag" style='padding:5px 20px;'>
	<h2>
		<?php _e( 'Filter the Products<br> <span style="color:green;font-size:16px">This basic version only supports Simple Products.<button id="go_premium_link" style="color:red; background:white; border:white; text-decoration: underline;">Go Premium</button>for Variable products.</span>', 'eh_bulk_edit' ); ?>
		<!--<span style="float: right;" id="calculate_percentage_back" ><span class='woocommerce-help-tip tooltip' id='calculate_percentage_back_tooltip' style="padding:0px 15px" data-tooltip='<?php _e( 'Check your Increase/Decrease Percentage to get back old price.', 'eh_bulk_edit' ); ?>'></span><button id='calculate_percentage_back_button' style="margin-bottom: 2%;" class='button button-primary button-large'><span class="calculate-percentage-back-text"><?php _e( 'Generate Percentage', 'eh_bulk_edit' ); ?></span></button></span>-->
		<span style="float: right;" id="remove_undo_update_button_top" ><span class='woocommerce-help-tip tooltip' id='add_undo_button_tooltip' style="padding:0px 15px" data-tooltip='<?php _e( 'Click to undo the last update you have done', 'eh_bulk_edit' ); ?>'></span><button id='undo_display_update_button' style="margin-bottom: 2%;" class='button button-primary button-large' disabled="disabled"><span class="update-text"><?php _e( 'Undo Last Update', 'eh_bulk_edit' ); ?></span></button></span>
	</h2>
	<hr>
	<table class='eh-content-table' id='data_table'>

		<tr>
			<td class='eh-content-table-left'>
				<?php _e( 'Product Title', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e( 'Select a condition from the drop-down and enter a product title', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<select id='product_title_select' style="width: 45%;">
					<option value='all'><?php _e( 'All', 'eh_bulk_edit' ); ?></option>
					<option value='starts_with'>Starts With</option>
					<option value='ends_with'>Ends With</option>
					<option value='contains'>Contains</option>
					<option value='title_regex'>RegEx Match</option>
				</select>
				<span id='product_title_text'></span>
			</td>
			<td class='eh-content-table-right' id='regex_flags_field'>
				<span class='select-eh'><select data-placeholder='<?php _e( 'Select Flags (Optional)', 'eh_bulk_edit' ); ?>' id='regex_flags_values' multiple class='category-chosen' >
						<?php
						{
							echo "<option value='A'>Anchored (A)</option>";
							echo "<option value='D'>Dollors End Only (D)</option>";
							echo "<option value='x'>Extended (x)</option>";
							echo "<option value='X'>Extra (X)</option>";
							// echo "<option value='g'>Global(g)</option>";
							echo "<option value='i'>Insensitive (i)</option>";
							echo "<option value='J'>Jchanged (J)</option>";
							echo "<option value='m'>Multi Line (m)</option>";
							echo "<option value='s'>Single Line (s)</option>";
							echo "<option value='u'>Unicode (u)</option>";
							echo "<option value='U'>Ungreedy (U)</option>";
						}
						?>
					</select></span>
			</td>
			<td class='eh-content-table-help_link' id='regex_help_link'>
				<a href="https://elextensions.com/understanding-regular-expression-regex-pattern-matching-bulk-edit-products-prices-attributes-woocommerce-plugin/" target="_blank">Help</a>
			</td>
		</tr>


		<tr>
			<td class='eh-content-table-left'>
				<?php _e( 'Product Types', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip=' <?php _e( 'Select the product type(s) for which the filter has to be applied', 'eh_bulk_edit' ); ?> '></span>
			</td>
			<td>

				<span class='select-eh'><select data-placeholder='<?php _e( 'Select Product Types', 'eh_bulk_edit' ); ?>' id='product_type' multiple class='category-chosen' >
						<?php
						{

							echo "<option value='simple'>Simple</option>";

						}
						?>
					</select></span>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php _e( 'Product Categories', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e( 'Select the category(s) for which the filter has to be applied. Enable the checkbox to include subcategories', 'eh_bulk_edit' ); ?>'></span>
			</td>

			<td class='eh-edit-tab-table-input-td'>
				<?php
				$cat_args      = array(
					'hide_empty'   => 0,
					'taxonomy'     => 'product_cat',
					'hierarchical' => 1,
					'orderby'      => 'name',
					'order'        => 'ASC',
					'child_of'     => 0,
				);
				$cat_hierarchy = elex_bep_filter_get_cat_hierarchy( 0, $cat_args );
				$cat_rows      = elex_bep_filter_category_rows( $cat_hierarchy, 0, 'cat_filter' );
				?>
				<div id="product_cat-all" class="tabs-panel">
					<ul id="product_catchecklist">
						<?php
						echo $cat_rows;
						?>
					</ul></div>
			</td>
			<td class='eh-content-table-right'>
				<input type="checkbox" id ="subcat_check">Include Subcategories
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php _e( 'Product Regular Price', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e( 'Select a condition and specify a price', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<select id='regular_price_range_select' style="width: 45%;">
					<option value='all'><?php _e( 'All', 'eh_bulk_edit' ); ?></option>
					<option value='>'>>=</option>
					<option value='<'><=</option>
					<option value='='>==</option>
					<option value='|'>|| <?php _e( 'Between', 'eh_bulk_edit' ); ?></option>
				</select>
				<span id='regular_price_range_text'></span>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
		<h3><?php _e( 'Attributes', 'eh_bulk_edit' ); ?></h3>
		<hr>
			</td>
	</tr>
		<tr id='attribute_types'>
			<td class='eh-content-table-left'>
				<?php _e( 'Product Attributes (Group with OR)', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class="woocommerce-help-tip tooltip" data-tooltip="<?php _e( "The products will be filtered when any one of the attributes and it's corresponding values are present", 'eh_bulk_edit' ); ?>"></span>
			</td>
			<td>
				<?php
				if ( count( $attributes ) > 0 ) {
					foreach ( $attributes as $key => $value ) {
						echo "<span id='attrib_name' class='checkbox-eh'><input type='checkbox' name='attrib_name' value='" . $value->attribute_name . "' id='" . $value->attribute_name . "'>" . $value->attribute_label . '</span>';
					}
				} else {
					echo "<span id='attrib_name' class='checkbox-eh'>No attributes found.</span>";
				}
				?>
			</td>
		</tr>
		<tr id='attribute_types_and'>
			<td class='eh-content-table-left'>
				<?php _e( 'Product Attributes (Group with AND)', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class="woocommerce-help-tip tooltip" data-tooltip="<?php _e( "The products will be filtered only when both attributes and it's corresponding values are present", 'eh_bulk_edit' ); ?>"></span>
			</td>
			<td>
				<?php
				if ( count( $attributes ) > 0 ) {
					foreach ( $attributes as $key => $value ) {
						echo "<span id='attrib_name_and' class='checkbox-eh'><input type='checkbox' name='attrib_name_and' value='" . $value->attribute_name . "' id='" . $value->attribute_name . "'>" . $value->attribute_label . '</span>';
					}
				} else {
					echo "<span id='attrib_name_and' class='checkbox-eh'>No attributes found.</span>";
				}
				?>
			</td>
		</tr>
		

	</table>
	<h2 >
		<?php _e( '<span style="padding-right:1em ; font-size:20px;">Exclusions</span> ', 'eh_bulk_edit' ); ?>
		<input type="checkbox" id ="enable_exclude_products"><span style="font-weight:normal;font-size: 14px;"><?php _e( 'Enable', 'eh_bulk_edit' ); ?></span>
	</h2>
	<hr align="left" width="20%" >
	<table class='eh-content-table' id="exclude_products">
		<tr>
			<td class='eh-content-table-left'>
				<?php _e( 'Exclude by IDs', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e( 'Enter the Product IDs to exclude from getting updated (separate IDs by comma).', 'eh_bulk_edit' ); ?>'></span>
			</td>
			<td class='eh-content-table-input-td'>
				<textarea rows="4" cols="50" id="exclude_ids"></textarea>
			</td>
		</tr>
		<tr>
			<td class='eh-content-table-left'>
				<?php _e( 'Exclude by Categories', 'eh_bulk_edit' ); ?>
			</td>
			<td class='eh-content-table-middle'>
				<span class='woocommerce-help-tip tooltip' data-tooltip="<?php _e( "Select the categories to exclude products from getting updated. All the subcategories under a parent category will be excluded if you enable 'Include Subcategories' checkbox.", 'eh_bulk_edit' ); ?>"></span>
			</td>
			<?php
			$cat_exclude_rows = elex_bep_filter_category_rows( $cat_hierarchy, 0, 'cat_exclude' );
			?>
			<td class='eh-edit-tab-table-input-td'>
				<div id="product_cat-all" class="tabs-panel">
					<ul id="product_catchecklist">
						<?php
						echo $cat_exclude_rows;
						?>
					</ul></div>
			</td>
			<td class='eh-content-table-right'>
				<input type="checkbox" id ="exclude_subcat_check">Include Subcategories
			</td>
		</tr>
	</table>
	<button id='clear_filter_button' value='clear_products' style='margin:5px 2px 2px 2px; color: white; width:15%; background-color: gray;' class='button button-large'><?php _e( 'Reset Filter', 'eh_bulk_edit' ); ?></button>
	<button id='filter_products_button' value='filter_products' style='margin:5px 2px 2px 2px; float: right; ' class='button button-primary button-large'><?php _e( 'Preview Filtered Products', 'eh_bulk_edit' ); ?></button>        
</div>

 <?php
	require_once 'market.php';

	require_once ELEX_BEP_TEMPLATE_PATH . '/elex-template-frontend-tables.php';

	function elex_bep_filter_get_cat_hierarchy( $parent, $args ) {
		$cats = get_categories( $args );
		$ret  = new stdClass();
		foreach ( $cats as $cat ) {
			if ( $cat->parent == $parent ) {
				$id                 = $cat->cat_ID;
				$ret->$id           = $cat;
				$ret->$id->children = elex_bep_filter_get_cat_hierarchy( $id, $args );
			}
		}
		return $ret;
	}

	function elex_bep_filter_category_rows( $categories, $level, $name ) {
		$html_code       = '';
		$level_indicator = '';
		for ( $i = 0; $i < $level; $i++ ) {
			$level_indicator .= '- ';
		}
		if ( $categories ) {
			foreach ( $categories as $category ) {
				$html_code .= '<li><label><input value=' . $category->slug . " type='checkbox' name=" . $name . '>' . $level_indicator . $category->name . '</label></li>';
				if ( $category->children && count( (array) $category->children ) > 0 ) {
					$html_code .= elex_bep_filter_category_rows( $category->children, $level + 1, $name );
				}
			}
		} else {
			$html_code .= esc_html__( 'No categories found.', 'eh_bulk_edit' );
		}
		return $html_code;
	}
