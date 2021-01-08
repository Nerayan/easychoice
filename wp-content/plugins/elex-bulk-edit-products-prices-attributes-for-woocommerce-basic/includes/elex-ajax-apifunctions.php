<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $hook_suffix;
add_action( 'wp_ajax_eh_bep_get_attributes_action', 'elex_bep_get_attributes_action_callback' );
add_action( 'wp_ajax_eh_bep_all_products', 'elex_bep_list_table_all_callback' );
add_action( 'wp_ajax_eh_bep_count_products', 'elex_bep_count_products_callback' );
add_action( 'wp_ajax_eh_bep_clear_products', 'elex_bep_clear_all_callback' );
add_action( 'wp_ajax_eh_bep_update_products', 'elex_bep_update_product_callback' );
add_action( 'wp_ajax_eh_bep_filter_products', 'elex_bep_search_filter_callback' );
add_action( 'wp_ajax_eh_bulk_edit_display_count', 'elex_bep_display_count_callback' );

function elex_bep_display_count_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$value = sanitize_text_field( $_POST['row_count'] );
	update_option( 'eh_bulk_edit_table_row', $value );
	die( 'success' );
}

function elex_bep_count_products_callback() {
	$filtered_products = elex_bep_get_selected_products();
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	die( json_encode( $filtered_products ) );
}

function elex_bep_get_attributes_action_callback() {
	$attribute_name = $_POST['attrib'];
	$cat_args       = array(
		'hide_empty' => false,
		'order'      => 'ASC',
	);
	$attributes     = wc_get_attribute_taxonomies();
	foreach ( $attributes as $key => $value ) {
		if ( $attribute_name == $value->attribute_name ) {
			$attribute_name  = $value->attribute_name;
			$attribute_label = $value->attribute_label;
		}
	}
	$attribute_value = get_terms( 'pa_' . $attribute_name, $cat_args );
	if ( isset( $_POST['attr_and'] ) ) {
		$return = "<optgroup label='" . $attribute_label . "' id='grp_and_" . $attribute_name . "'>";
	} else {
		$return = "<optgroup label='" . $attribute_label . "' id='grp_" . $attribute_name . "'>";
	}
	foreach ( $attribute_value as $key => $value ) {
		$return .= "<option value=\"'pa_" . $attribute_name . ':' . $value->slug . "'\">" . $value->name . '</option>';
	}
	$return .= '</optgroup>';
	echo $return;
	exit;
}

// custom rounding


function elex_bep_round_ceiling( $number, $significance = 1 ) {
	return ( is_numeric( $number ) && is_numeric( $significance ) ) ? ( ceil( $number / $significance ) * $significance ) : false;
}

function elex_bep_update_product_callback() {
	set_time_limit( 300 );
		// HTML tags and attributes allowed in description and short description
	$allowed_html = wp_kses_allowed_html( 'post' );
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$selected_products    = array_diff( $_POST['pid'], json_decode( $_POST['unchecked_array'] ) );
	$product_data         = array();

	$title_select         = sanitize_text_field( $_POST['title_select'] );
	$title_text               = sanitize_text_field( $_POST['title_text'] );
	$replace_title_text       = sanitize_text_field( $_POST['replace_title_text'] );
	$regex_replace_title_text = sanitize_text_field( $_POST['regex_replace_title_text'] );

	//

	$sku_select           = sanitize_text_field( $_POST['sku_select'] );
	$sku_text                 = sanitize_text_field( $_POST['sku_text'] );
	$sku_replace_text         = sanitize_text_field( $_POST['sku_replace_text'] );
	$regex_sku_replace_text   = sanitize_text_field( $_POST['regex_sku_replace_text'] );
	
	#sale price options
	$sale_select          = sanitize_text_field( $_POST['sale_select'] );
	$sale_text                = sanitize_text_field( $_POST['sale_text'] );
	$sale_round_select    = sanitize_text_field( $_POST['sale_round_select'] );
	$sale_round_text          = isset( $_POST['sale_round_text'] ) ? sanitize_text_field( $_POST['sale_round_text'] ) : '';
	$sale_warning = array();
	#regular price options
	$regular_select       = sanitize_text_field( $_POST['regular_select'] );
	$regular_round_select = sanitize_text_field( $_POST['regular_round_select'] );
	$regular_text             = sanitize_text_field( $_POST['regular_text'] );
	$regular_round_text       = isset( $_POST['regular_round_text'] ) ? sanitize_text_field( $_POST['regular_round_text'] ) : '';


	$catalog_select       = sanitize_text_field( $_POST['catalog_select'] );
	$shipping_select      = sanitize_text_field( $_POST['shipping_select'] );
	$shipping_unit            = sanitize_text_field( $_POST['shipping_unit'] );
	$shipping_unit_select     = sanitize_text_field( $_POST['shipping_unit_select'] );

	$stock_manage_select  = sanitize_text_field( $_POST['stock_manage_select'] );
	$stock_status_select  = sanitize_text_field( $_POST['stock_status_select'] );

	$quantity_select      = sanitize_text_field( $_POST['quantity_select'] );
	$quantity_text            = sanitize_text_field( $_POST['quantity_text'] );

	$backorder_select     = sanitize_text_field( $_POST['backorder_select'] );
	$attribute_action     = sanitize_text_field( $_POST['attribute_action'] );

	$length_select            = sanitize_text_field( $_POST['length_select'] );
	$width_select             = sanitize_text_field( $_POST['width_select'] );
	$height_select            = sanitize_text_field( $_POST['height_select'] );
	$weight_select            = sanitize_text_field( $_POST['weight_select'] );
	$length_text              = sanitize_text_field( $_POST['length_text'] );
	$width_text               = sanitize_text_field( $_POST['width_text'] );
	$height_text              = sanitize_text_field( $_POST['height_text'] );
	$weight_text              = sanitize_text_field( $_POST['weight_text'] );
	

	$hide_price               = sanitize_text_field( $_POST['hide_price'] );
	$hide_price_role          = ( $_POST['hide_price_role'] != '' ) ? sanitize_text_field( $_POST['hide_price_role'] ) : '';
	$price_adjustment         = sanitize_text_field( $_POST['price_adjustment'] );
	$featured                 = sanitize_text_field( $_POST['is_featured'] );

	$description              = wp_kses( $_POST['description'], $allowed_html );
	$description_action       = sanitize_text_field( $_POST['description_action'] );
	$short_description        = wp_kses( $_POST['short_description'], $allowed_html );
	$short_description_action = sanitize_text_field( $_POST['short_description_action'] );

	$gallery_images           = ! empty( $_POST['gallery_images'] ) ? array_map( 'sanitize_text_field', $_POST['gallery_images'] ) : array( '' );
	$gallery_images_action    = sanitize_text_field( $_POST['gallery_images_action'] );
	$main_image               = sanitize_text_field( $_POST['main_image'] );

	$delete_product_action    = sanitize_text_field( $_POST['delete_product_action'] );

	$tax_status_action        = sanitize_text_field( $_POST['tax_status_action'] );
	$tax_class_action         = sanitize_text_field( $_POST['tax_class_action'] );

	$count_iteration = 0;

	foreach ( $selected_products as $pid => $temp ) {
		$pid = $temp;
		apply_filters( 'http_request_timeout', 3 );
		switch ( $hide_price ) {
			case 'yes':
				elex_bep_update_meta_fn( $pid, 'product_adjustment_hide_price_unregistered', 'yes' );
				break;
			case 'no':
				elex_bep_update_meta_fn( $pid, 'product_adjustment_hide_price_unregistered', 'no' );
				break;
		}
		switch ( $price_adjustment ) {
			case 'yes':
				elex_bep_update_meta_fn( $pid, 'product_based_price_adjustment', 'yes' );
				break;
			case 'no':
				elex_bep_update_meta_fn( $pid, 'product_based_price_adjustment', 'no' );
				break;
		}
		if ( $hide_price_role != '' ) {
			elex_bep_update_meta_fn( $pid, 'eh_pricing_adjustment_product_price_user_role', $hide_price_role );
		}
		switch ( $shipping_unit_select ) {
			case 'add':
				$unit     = get_post_meta( $pid, '_wf_shipping_unit', true );
				$unit_val = number_format( $unit + $shipping_unit, 6, '.', '' );
				elex_bep_update_meta_fn( $pid, '_wf_shipping_unit', $unit_val );
				break;
			case 'sub':
				$unit     = get_post_meta( $pid, '_wf_shipping_unit', true );
				$unit_val = number_format( $unit - $shipping_unit, 6, '.', '' );
				elex_bep_update_meta_fn( $pid, '_wf_shipping_unit', $unit_val );
				break;
			case 'replace':
				$unit = get_post_meta( $pid, '_wf_shipping_unit', true );
				elex_bep_update_meta_fn( $pid, '_wf_shipping_unit', $shipping_unit );
				break;
			default:
				break;
		}

		$temp      = wc_get_product( $pid );
		$parent    = $temp;
		$parent_id = $pid;
		if ( ! empty( $temp ) && $temp->is_type( 'variation' ) ) {
			$parent_id = ( WC()->version < '2.7.0' ) ? $temp->parent->id : $temp->get_parent_id();
			$parent    = wc_get_product( $parent_id );
		}
		
		$temp_type  = ( WC()->version < '2.7.0' ) ? $temp->product_type : $temp->get_type();
		$temp_title = ( WC()->version < '2.7.0' ) ? $temp->post->post_title : $temp->get_title();
		if ( $temp_type == 'simple' || $temp_type == 'variation' || $temp_type == 'variable' ) {
			$product_data                   = array();
			$product_data['type']           = 'simple';
			$product_data['title']          = $temp_title;
			$product_data['sku']            = get_post_meta( $pid, '_sku', true );
			$product_data['catalog']        = ( WC()->version < '3.0.0' ) ? get_post_meta( $pid, '_visibility', true ) : $temp->get_catalog_visibility();
			$ship_args                      = array( 'fields' => 'ids' );
			$product_data['shipping']       = current( wp_get_object_terms( $pid, 'product_shipping_class', $ship_args ) );
			$product_data['sale']           = (float) get_post_meta( $pid, '_sale_price', true );
			$product_data['regular']        = (float) get_post_meta( $pid, '_regular_price', true );
			$product_data['stock_manage']   = get_post_meta( $pid, '_manage_stock', true );
			$product_data['stock_quantity'] = (float) get_post_meta( $pid, '_stock', true );
			$product_data['backorder']      = get_post_meta( $pid, '_backorders', true );
			$product_data['stock_status']   = get_post_meta( $pid, '_stock_status', true );
			$product_data['length']         = (float) get_post_meta( $pid, '_length', true );
			$product_data['width']          = (float) get_post_meta( $pid, '_width', true );
			$product_data['height']         = (float) get_post_meta( $pid, '_height', true );
			$product_data['weight']         = (float) get_post_meta( $pid, '_weight', true );

			switch ( $title_select ) {
				case 'set_new':
					$my_post = array(
						'ID'         => $pid,
						'post_title' => $title_text,
					);
					wp_update_post( $my_post );
					break;
				case 'append':
					$my_post = array(
						'ID'         => $pid,
						'post_title' => $product_data['title'] . $title_text,
					);
					wp_update_post( $my_post );
					break;
				case 'prepand':
					$my_post = array(
						'ID'         => $pid,
						'post_title' => $title_text . $product_data['title'],
					);
					wp_update_post( $my_post );
					break;
				case 'replace':
					$my_post = array(
						'ID'         => $pid,
						'post_title' => str_replace( $replace_title_text, $title_text, $product_data['title'] ),
					);
					wp_update_post( $my_post );
					break;
				case 'regex_replace':
					if ( @preg_replace( '/' . $regex_replace_title_text . '/', $title_text, $product_data['title'] ) != false ) {
						$regex_flags = '';
						if ( ! empty( $_REQUEST['regex_flag_sele_title'] ) ) {
							foreach ( $_REQUEST['regex_flag_sele_title'] as $reg_val ) {
								$regex_flags .= sanitize_text_field( $reg_val );
							}
						}
						$my_post = array(
							'ID'         => $pid,
							'post_title' => preg_replace( '/' . $regex_replace_title_text . '/' . $regex_flags, $title_text, $product_data['title'] ),
						);
						wp_update_post( $my_post );
					}
					break;
			}
			switch ( $sku_select ) {
				case 'set_new':
					elex_bep_update_meta_fn( $pid, '_sku', $sku_text );
					break;
				case 'append':
					$sku_val = $product_data['sku'] . $sku_text;
					elex_bep_update_meta_fn( $pid, '_sku', $sku_val );
					break;
				case 'prepand':
					$sku_val = $sku_text . $product_data['sku'];
					elex_bep_update_meta_fn( $pid, '_sku', $sku_val );
					break;
				case 'replace':
					$sku_val = str_replace( $sku_replace_text, $sku_text, $product_data['sku'] );
					elex_bep_update_meta_fn( $pid, '_sku', $sku_val );
					break;
				case 'regex_replace':
					if ( @preg_replace( '/' . $regex_sku_replace_text . '/', $sku_text, $product_data['sku'] ) != false ) {
						$regex_flags = '';
						if ( ! empty( $_REQUEST['regex_flag_sele_sku'] ) ) {
							foreach ( $_REQUEST['regex_flag_sele_sku'] as $reg_val ) {
								$regex_flags .= sanitize_text_field( $reg_val );
							}
						}
						$sku_val = preg_replace( '/' . $regex_sku_replace_text . '/' . $regex_flags, $sku_text, $product_data['sku'] );
						elex_bep_update_meta_fn( $pid, '_sku', $sku_val );
					}
					break;
			}
			// Set featured
			if ( isset( $_REQUEST['is_featured'] ) && ! empty( $_REQUEST['is_featured'] ) ) {
				$parent->set_featured( $featured );
				$parent->save();
			}
			$product_details = $temp->get_data();
			// Product description
			if ( isset( $description ) && '' != $description && '' != $description_action ) {
				if ( 'append' == $description_action ) {
					$desc = $product_details['description'] . $description;
				} elseif ( 'prepend' == $description_action ) {
					$desc = $description . $product_details['description'];
				} else {
					$desc = $description;
				}
				$temp->set_description( $desc );
				$temp->save();
			}
			// Product short description
			if ( isset( $short_description ) && '' != $short_description && '' != $short_description_action ) {
				if ( 'append' == $short_description_action ) {
					$short_desc = $product_details['short_description'] . $short_description;
				} elseif ( 'prepend' == $short_description_action ) {
					$short_desc = $short_description . $product_details['short_description'];
				} else {
					$short_desc = $short_description;
				}
				$temp->set_short_description( $short_desc );
				$temp->save();
			}
			// Main image
			$edit_data['main_image'] = '';
			if ( isset( $main_image ) && $main_image ) {
				$edit_data['main_image'] = $temp->get_image_id();
				$image_id                = attachment_url_to_postid( $main_image );
				$temp->set_image_id( $image_id );
				$temp->save();
			}
			// Gallery images
			if ( isset( $gallery_images ) && '' != $gallery_images && '' != $gallery_images_action ) {
				$gallery_image_ids = array();
				foreach ( $gallery_images as $image_index => $image_url ) {
					$gallery_image_id = attachment_url_to_postid( $image_url );
					array_push( $gallery_image_ids, $gallery_image_id );
				}
				if ( 'add' == $gallery_images_action ) {
					$gallery_image_ids = array_merge( $gallery_image_ids, $temp->get_gallery_image_ids() );
				} elseif ( 'remove' == $gallery_images_action ) {
					$flag_array = array();
					if ( ! empty( $temp->get_gallery_image_ids() ) ) {
						foreach ( $temp->get_gallery_image_ids() as $i_ids ) {
							if ( ! in_array( $i_ids, $gallery_image_ids ) ) {
								array_push( $flag_array, $i_ids );
							}
						}
					}
					if ( ! empty( $flag_array ) ) {
						$gallery_image_ids = $flag_array;
					}
				}
				$temp->set_gallery_image_ids( $gallery_image_ids );
				$temp->save();
			}
			// Delete
			if ( isset( $delete_product_action ) && '' != $delete_product_action ) {
				if ( 'move_to_trash' == $delete_product_action ) {
					$temp->delete( false );
				} else {
					$temp->delete( true );
				}
			}
			if ( $temp_type != 'variation' ) {
				if ( WC()->version < '3.0.0' ) {
					elex_bep_update_meta_fn( $pid, '_visibility', $catalog_select );
				} else {
					$options        = array_keys( wc_get_product_visibility_options() );
					$catalog_select = wc_clean( $catalog_select );
					if ( in_array( $catalog_select, $options, true ) ) {
						$parent->set_catalog_visibility( $catalog_select );
						$parent->save();
					}
				}
			}

			if ( $shipping_select != '' ) {
				wp_set_object_terms( (int) $pid, (int) $shipping_select, 'product_shipping_class' );
			}

			switch ( $regular_select ) {
				case 'up_percentage':
					if ( $product_data['regular'] !== '' ) {
						$per_val = $product_data['regular'] * ( $regular_text / 100 );
						$cal_val = $product_data['regular'] + $per_val;
						if ( $regular_round_select != '' ) {
							if ( $regular_round_text == '' ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = elex_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = elex_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );

						$sal_val = get_post_meta( $pid, '_sale_price', true );
						if ( $temp_type != 'variable' && $sal_val < $regular_val ) {
							elex_bep_update_meta_fn( $pid, '_regular_price', $regular_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Regular' );
							array_push( $sale_warning, $temp_type );
						}
					}
					break;
				case 'down_percentage':

					if ( $product_data['regular'] !== '' ) {
						$per_val = $product_data['regular'] * ( $regular_text / 100 );
						$cal_val = $product_data['regular'] - $per_val;
						if ( $regular_round_select != '' ) {
							if ( $regular_round_text == '' ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = elex_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = elex_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );

						$sal_val = get_post_meta( $pid, '_sale_price', true );
						if ( $temp_type != 'variable' && $sal_val < $regular_val ) {
							elex_bep_update_meta_fn( $pid, '_regular_price', $regular_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Regular' );
							array_push( $sale_warning, $temp_type );
						}
					}
					break;
				case 'up_price':
					if ( $product_data['regular'] !== '' ) {
						$cal_val = $product_data['regular'] + $regular_text;
						if ( $regular_round_select != '' ) {
							if ( $regular_round_text == '' ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = elex_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = elex_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );

						$sal_val = get_post_meta( $pid, '_sale_price', true );
						if ( $temp_type != 'variable' && $sal_val < $regular_val ) {
							elex_bep_update_meta_fn( $pid, '_regular_price', $regular_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Regular' );
							array_push( $sale_warning, $temp_type );
						}
					}
					break;
				case 'down_price':
					if ( $product_data['regular'] !== '' ) {
						$cal_val = $product_data['regular'] - $regular_text;
						if ( $regular_round_select != '' ) {
							if ( $regular_round_text == '' ) {
								$regular_round_text = 1;
							}
							$got_regular = $cal_val;
							switch ( $regular_round_select ) {
								case 'up':
									$cal_val = elex_bep_round_ceiling( $got_regular, $regular_round_text );
									break;
								case 'down':
									$cal_val = elex_bep_round_ceiling( $got_regular, -$regular_round_text );
									break;
							}
						}
						$regular_val = wc_format_decimal( $cal_val, '', true );
						$sal_val     = get_post_meta( $pid, '_sale_price', true );
						if ( $temp_type != 'variable' && $sal_val < $regular_val ) {
							elex_bep_update_meta_fn( $pid, '_regular_price', $regular_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Regular' );
							array_push( $sale_warning, $temp_type );
						}
					}
					break;
				case 'flat_all':
					$regular_val = wc_format_decimal( $regular_text, '', true );
					$sal_val     = get_post_meta( $pid, '_sale_price', true );
					if ( $temp_type != 'variable' && $sal_val < $regular_val ) {
						elex_bep_update_meta_fn( $pid, '_regular_price', $regular_val );
					} else {
						array_push( $sale_warning, $pid, $parent_id );
						array_push( $sale_warning, 'Regular' );
						array_push( $sale_warning, $temp_type );
					}
					break;
			}
			switch ( $sale_select ) {
				case 'up_percentage':
					if ( $product_data['sale'] !== '' ) {
						$per_val = $product_data['sale'] * ( $sale_text / 100 );
						$cal_val = $product_data['sale'] + $per_val;
						if ( $sale_round_select != '' ) {
							if ( $sale_round_text == '' ) {
								$sale_round_text = 1;
							}
							$got_sale = $cal_val;
							switch ( $sale_round_select ) {
								case 'up':
									$cal_val = elex_bep_round_ceiling( $got_sale, $sale_round_text );
									break;
								case 'down':
									$cal_val = elex_bep_round_ceiling( $got_sale, -$sale_round_text );
									break;
							}
						}
						$sale_val = wc_format_decimal( $cal_val, '', true );
						// leave sale price blank if sale price increased by -100%
						if ( $sale_val == 0 ) {
							$sale_val = '';
						}
						$reg_val = get_post_meta( $pid, '_regular_price', true );
						if ( $temp_type != 'variable' && $sale_val < $reg_val ) {
							elex_bep_update_meta_fn( $pid, '_sale_price', $sale_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Sales' );
							array_push( $sale_warning, $temp_type );
							if ( isset( $_POST['regular_select'] ) ) {
								elex_bep_update_meta_fn( $pid, '_regular_price', $product_data['regular'] );
							}
						}
					}
					break;
				case 'down_percentage':
					if ( $product_data['sale'] !== '' || $_POST['regular_check_val'] ) {
						if ( $_POST['regular_check_val'] ) {
							if ( $product_data['regular'] == '' ) {
								break;
							}
							$per_val = $product_data['regular'] * ( $sale_text / 100 );
							$cal_val = $product_data['regular'] - $per_val;
						} else {
							$per_val = $product_data['sale'] * ( $sale_text / 100 );
							$cal_val = $product_data['sale'] - $per_val;
						}
						if ( $sale_round_select != '' ) {
							if ( $sale_round_text == '' ) {
								$sale_round_text = 1;
							}
							$got_sale = $cal_val;
							switch ( $sale_round_select ) {
								case 'up':
									$cal_val = elex_bep_round_ceiling( $got_sale, $sale_round_text );
									break;
								case 'down':
									$cal_val = elex_bep_round_ceiling( $got_sale, -$sale_round_text );
									break;
							}
						}
						$sale_val = wc_format_decimal( $cal_val, '', true );
						// leave sale price blank if sale price decreased by 100%
						if ( $sale_val == 0 ) {
							$sale_val = '';
						}
						$reg_val = get_post_meta( $pid, '_regular_price', true );
						if ( $temp_type != 'variable' && $sale_val < $reg_val ) {
							elex_bep_update_meta_fn( $pid, '_sale_price', $sale_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Sales' );
							array_push( $sale_warning, $temp_type );
							if ( isset( $_POST['regular_select'] ) ) {
								elex_bep_update_meta_fn( $pid, '_regular_price', $product_data['regular'] );
							}
						}
					}
					break;
				case 'up_price':
					if ( $product_data['sale'] !== '' ) {
						$cal_val = $product_data['sale'] + $sale_text;
						if ( $sale_round_select != '' ) {
							if ( $sale_round_text == '' ) {
								$sale_round_text = 1;
							}
							$got_sale = $cal_val;
							switch ( $sale_round_select ) {
								case 'up':
									$cal_val = elex_bep_round_ceiling( $got_sale, $sale_round_text );
									break;
								case 'down':
									$cal_val = elex_bep_round_ceiling( $got_sale, -$sale_round_text );
									break;
							}
						}
						$sale_val = wc_format_decimal( $cal_val, '', true );
						$reg_val  = get_post_meta( $pid, '_regular_price', true );
						if ( $temp_type != 'variable' && $sale_val < $reg_val ) {
							elex_bep_update_meta_fn( $pid, '_sale_price', $sale_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Sales' );
							array_push( $sale_warning, $temp_type );
							if ( isset( $_POST['regular_select'] ) ) {
								elex_bep_update_meta_fn( $pid, '_regular_price', $product_data['regular'] );
							}
						}
					}
					break;
				case 'down_price':
					if ( $product_data['sale'] !== '' || $_POST['regular_check_val'] ) {
						if ( $_POST['regular_check_val'] ) {
							if ( $product_data['regular'] == '' ) {
								break;
							}
							$cal_val = $product_data['regular'] - $sale_text;
						} else {
							$cal_val = $product_data['sale'] - $sale_text;
						}

						if ( $sale_round_select != '' ) {
							if ( $sale_round_text == '' ) {
								$sale_round_text = 1;
							}
							$got_sale = $cal_val;
							switch ( $sale_round_select ) {
								case 'up':
									$cal_val = elex_bep_round_ceiling( $got_sale, $sale_round_text );
									break;
								case 'down':
									$cal_val = elex_bep_round_ceiling( $got_sale, -$sale_round_text );
									break;
							}
						}
						$sale_val = wc_format_decimal( $cal_val, '', true );
						$reg_val  = get_post_meta( $pid, '_regular_price', true );
						if ( $temp_type != 'variable' && $sale_val < $reg_val ) {
							elex_bep_update_meta_fn( $pid, '_sale_price', $sale_val );
						} else {
							array_push( $sale_warning, $pid, $parent_id );
							array_push( $sale_warning, 'Sales' );
							array_push( $sale_warning, $temp_type );
							if ( isset( $_POST['regular_select'] ) ) {
								elex_bep_update_meta_fn( $pid, '_regular_price', $product_data['regular'] );
							}
						}
					}
					break;
				case 'flat_all':
					$sale_val = wc_format_decimal( $sale_text, '', true );
					$reg_val  = get_post_meta( $pid, '_regular_price', true );
					if ( $temp_type != 'variable' && $sale_val < $reg_val ) {
						elex_bep_update_meta_fn( $pid, '_sale_price', $sale_val );
					} else {
						array_push( $sale_warning, $pid, $parent_id );
						array_push( $sale_warning, 'Sales' );
						array_push( $sale_warning, $temp_type );
						if ( isset( $_POST['regular_select'] ) ) {
							elex_bep_update_meta_fn( $pid, '_regular_price', $product_data['regular'] );
						}
					}
					break;
			}


			if ( get_post_meta( $pid, '_sale_price', true ) !== '' && get_post_meta( $pid, '_regular_price', true ) !== '' ) {
				elex_bep_update_meta_fn( $pid, '_price', get_post_meta( $pid, '_sale_price', true ) );
			} elseif ( get_post_meta( $pid, '_sale_price', true ) === '' && get_post_meta( $pid, '_regular_price', true ) !== '' ) {
				elex_bep_update_meta_fn( $pid, '_price', get_post_meta( $pid, '_regular_price', true ) );
			} elseif ( get_post_meta( $pid, '_sale_price', true ) !== '' && get_post_meta( $pid, '_regular_price', true ) === '' ) {
				elex_bep_update_meta_fn( $pid, '_price', get_post_meta( $pid, '_sale_price', true ) );
			} elseif ( get_post_meta( $pid, '_sale_price', true ) === '' && get_post_meta( $pid, '_regular_price', true ) === '' ) {
				elex_bep_update_meta_fn( $pid, '_price', '' );
			}
			switch ( $stock_manage_select ) {
				case 'yes':
					elex_bep_update_meta_fn( $pid, '_manage_stock', 'yes' );
					break;
				case 'no':
					elex_bep_update_meta_fn( $pid, '_manage_stock', 'no' );
					break;
			}
			switch ( $tax_status_action ) {
				case 'taxable':
					elex_bep_update_meta_fn( $pid, '_tax_status', $tax_status_action );
					break;
				case 'shipping':
					elex_bep_update_meta_fn( $pid, '_tax_status', $tax_status_action );
					break;
				case 'none':
					elex_bep_update_meta_fn( $pid, '_tax_status', $tax_status_action );
					break;
			}
			if ( 'default' == $tax_class_action ) {
				elex_bep_update_meta_fn( $pid, '_tax_class', '' );
			} else {
				elex_bep_update_meta_fn( $pid, '_tax_class', $tax_class_action );
			}
			switch ( $quantity_select ) {
				case 'add':
					$quantity_val = number_format( $product_data['stock_quantity'] + $quantity_text, 6, '.', '' );
					elex_bep_update_meta_fn( $pid, '_stock', $quantity_val );
					break;
				case 'sub':
					$quantity_val = number_format( $product_data['stock_quantity'] - $quantity_text, 6, '.', '' );
					elex_bep_update_meta_fn( $pid, '_stock', $quantity_val );
					break;
				case 'replace':
					$quantity_val = number_format( $quantity_text, 6, '.', '' );
					elex_bep_update_meta_fn( $pid, '_stock', $quantity_val );
					break;
			}
			switch ( $backorder_select ) {
				case 'no':
					elex_bep_update_meta_fn( $pid, '_backorders', 'no' );
					break;
				case 'notify':
					elex_bep_update_meta_fn( $pid, '_backorders', 'notify' );
					break;
				case 'yes':
					elex_bep_update_meta_fn( $pid, '_backorders', 'yes' );
					break;
			}
			switch ( $stock_status_select ) {
				case 'instock':
					elex_bep_update_meta_fn( $pid, '_stock_status', 'instock' );
					break;
				case 'outofstock':
					elex_bep_update_meta_fn( $pid, '_stock_status', 'outofstock' );
					break;
				case 'onbackorder':
					elex_bep_update_meta_fn( $pid, '_stock_status', 'onbackorder' );
					break;
			}
			switch ( $length_select ) {
				case 'add':
					$length_val = $product_data['length'] + $length_text;
					elex_bep_update_meta_fn( $pid, '_length', $length_val );
					break;
				case 'sub':
					$length_val = $product_data['length'] - $length_text;
					elex_bep_update_meta_fn( $pid, '_length', $length_val );
					break;
				case 'replace':
					$length_val = $length_text;
					elex_bep_update_meta_fn( $pid, '_length', $length_val );
					break;
			}
			switch ( $width_select ) {
				case 'add':
					$width_val = $product_data['width'] + $width_text;
					elex_bep_update_meta_fn( $pid, '_width', $width_val );
					break;
				case 'sub':
					$width_val = $product_data['width'] - $width_text;
					elex_bep_update_meta_fn( $pid, '_width', $width_val );
					break;
				case 'replace':
					$width_val = $width_text;
					elex_bep_update_meta_fn( $pid, '_width', $width_val );
					break;
			}
			switch ( $height_select ) {
				case 'add':
					$height_val = $product_data['height'] + $height_text;
					elex_bep_update_meta_fn( $pid, '_height', $height_val );
					break;
				case 'sub':
					$height_val = $product_data['height'] - $height_text;
					elex_bep_update_meta_fn( $pid, '_height', $height_val );
					break;
				case 'replace':
					$height_val = $height_text;
					elex_bep_update_meta_fn( $pid, '_height', $height_val );
					break;
			}
			switch ( $weight_select ) {
				case 'add':
					$weight_val = $product_data['weight'] + $weight_text;
					elex_bep_update_meta_fn( $pid, '_weight', $weight_val );
					break;
				case 'sub':
					$weight_val = $product_data['weight'] - $weight_text;
					elex_bep_update_meta_fn( $pid, '_weight', $weight_val );
					break;
				case 'replace':
					$weight_val = $weight_text;
					elex_bep_update_meta_fn( $pid, '_weight', $weight_val );
					break;
			}
			wc_delete_product_transients( $pid );
		}

				// Edit Attributes
		if ( $temp_type != 'variation' && ! empty( $_POST['attribute'] ) ) {
			$i                   = 0;
			$is_variation        = 0;
			$prev_value          = '';
			$_product_attributes = get_post_meta( $pid, '_product_attributes', true );
			$attr_undo           = $_product_attributes;
			foreach ( $attr_undo as $key => $val ) {
				$attr_undo[ $key ]['value'] = wc_get_product_terms( $pid, $key );
			}
			if ( sanitize_text_field( $_POST['attribute_variation'] ) == 'add' ) {
				$is_variation = 1;
			}
			if ( sanitize_text_field( $_POST['attribute_variation'] ) == 'remove' ) {
				$is_variation = 0;
			}

			if ( ! empty( $_POST['attribute_value'] ) ) {
				foreach ( $_POST['attribute_value'] as $key => $value ) {

					$value     = stripslashes( sanitize_text_field( $value ) );
					$value     = preg_replace( '/\'/', '', $value );
					$att_slugs = explode( ':', $value );
					if ( $_POST['attribute_variation'] == '' && isset( $_product_attributes[ $att_slugs[0] ] ) ) {
						$is_variation = $_product_attributes[ $att_slugs[0] ]['is_variation'];
					}
					if ( $prev_value != $att_slugs[0] ) {
						$i = 0;
					}
					$prev_value = $att_slugs[0];
					if ( sanitize_text_field( $_POST['attribute_action'] ) == 'replace' && $i == 0 ) {
						wp_set_object_terms( $pid, $att_slugs[1], $att_slugs[0] );
						$i++;
					} else {
						wp_set_object_terms( $pid, $att_slugs[1], $att_slugs[0], true );
					}
					$thedata = array(
						$att_slugs[0] => array(
							'name'         => $att_slugs[0],
							'value'        => $att_slugs[1],
							'is_visible'   => '1',
							'is_taxonomy'  => '1',
							'is_variation' => $is_variation,
						),
					);
					if ( sanitize_text_field( $_POST['attribute_action'] ) == 'add' || sanitize_text_field( $_POST['attribute_action'] ) == 'replace' ) {
						$_product_attr = get_post_meta( $pid, '_product_attributes', true );
						if ( ! empty( $_product_attr ) ) {
							update_post_meta( $pid, '_product_attributes', array_merge( $_product_attr, $thedata ) );
						} else {
							update_post_meta( $pid, '_product_attributes', $thedata );
						}
					}
					if ( sanitize_text_field( $_POST['attribute_action'] ) == 'remove' ) {
						wp_remove_object_terms( $pid, $att_slugs[1], $att_slugs[0] );
					}
				}
			}
			if ( ! empty( $_POST['new_attribute_values'] ) || $_POST['new_attribute_values'] != '' ) {
				$ar1 = explode( ',', sanitize_text_field( $_POST['attribute'] ) );
				foreach ( $ar1 as $key => $value ) {
					foreach ( $_POST['new_attribute_values'] as $key_index => $value_slug ) {

						$att_s = 'pa_' . $value;

						if ( $prev_value != $att_s ) {
							$i = 0;
						}

						if ( $_POST['attribute_variation'] == '' && isset( $_product_attributes[ $att_s ] ) ) {
							$is_variation = $_product_attributes[ $att_s ]['is_variation'];
						}

						$prev_value = $att_s;
						if ( sanitize_text_field( $_POST['attribute_action'] ) == 'replace' && $i == 0 ) {
							wp_set_object_terms( $pid, $value_slug, $att_s );
							$i++;
						} else {
							wp_set_object_terms( $pid, $value_slug, $att_s, true );
						}
						$thedata = array(
							$att_s => array(
								'name'         => $att_s,
								'value'        => $value_slug,
								'is_visible'   => '1',
								'is_taxonomy'  => '1',
								'is_variation' => $is_variation,
							),
						);
						if ( sanitize_text_field( $_POST['attribute_action'] == 'add' ) || sanitize_text_field( $_POST['attribute_action'] ) == 'replace' ) {
							$_product_attr = get_post_meta( $pid, '_product_attributes', true );
							if ( ! empty( $_product_attr ) ) {
								update_post_meta( $pid, '_product_attributes', array_merge( $_product_attr, $thedata ) );
							} else {
								update_post_meta( $pid, '_product_attributes', $thedata );
							}
						}
					}
				}
			}
		}
				// category feature
		if ( sanitize_text_field( $_POST['category_update_option'] ) != 'cat_none' && isset( $_POST['categories_to_update'] ) ) {
			$existing_cat = wp_get_object_terms( $pid, 'product_cat' );
			// undo data
			
			if ( sanitize_text_field( $_POST['category_update_option'] ) == 'cat_add' ) {
				$temparr = array();
				foreach ( $existing_cat as $cat_key => $cat_val ) {
					array_push( $temparr, (int) $cat_val->term_id );
				}
				foreach ( $_POST['categories_to_update'] as $key => $value ){
					if ( ! in_array(  (int) $value, $temparr, true )) {
						array_push( $temparr, (int) $value);
					}
					wp_set_object_terms( $pid, $temparr, 'product_cat' );
				}
			}
			elseif (  sanitize_text_field( $_POST['category_update_option'] ) == 'cat_replace' ) {
				$temparr = array();
				foreach ( $_POST['categories_to_update'] as $key => $val ) {
					array_push( $temparr, (int) $val );
					}
					wp_set_object_terms( $pid, $temparr, 'product_cat' );
			}
			elseif ( sanitize_text_field( $_POST['category_update_option'] ) == 'cat_remove' ) {
				$temparr_remove = array();
				foreach ( $existing_cat as $cat_rem_key => $cat_rem_val ) {
					
					if ( ! in_array( (int) $cat_rem_val->term_id, $_POST['categories_to_update'] ) ) {
						array_push( $temparr_remove, (int) $cat_rem_val->term_id );
					}
				}
				wp_set_object_terms( $pid, $temparr_remove, 'product_cat' );
			}	
		}
		$count_iteration = $count_iteration + 1;
	}
	if ( $_POST['index_val'] == $_POST['chunk_length'] - 1 ) {
		array_push( $sale_warning, 'done' );
		die( json_encode( $sale_warning ) );
	}
	die( json_encode( $sale_warning ) );
}

function elex_bep_update_meta_fn( $id, $key, $value ) {
	update_post_meta( $id, $key, $value );
}

function elex_bep_list_table_all_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$obj = new Elex_DataTables();
	$obj->input();
	$obj->ajax_response( '1' );
}

function elex_bep_clear_all_callback() {
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	update_option( 'eh_bulk_edit_choosed_product_id', elex_bep_get_first_products() );
	$obj = new Elex_DataTables();
	$obj->input();
	$obj->ajax_response();
}

function elex_bep_search_filter_callback() {
	set_time_limit( 300 );
	check_ajax_referer( 'ajax-eh-bep-nonce', '_ajax_eh_bep_nonce' );
	$obj_fil = new Elex_DataTables();
	$obj_fil->input();
	$obj_fil->ajax_response( '1' );
}


function elex_bep_get_selected_products( $table_obj = null ) {
	$sel_ids = array();
	if ( isset( $_REQUEST['count_products'] ) ) {
		$sel_ids = get_option( 'xa_bulk_selected_ids' );
		return $sel_ids;
	}
	delete_option( 'xa_bulk_selected_ids' );
	$page_no           = ! empty( $_REQUEST['paged'] ) ? sanitize_text_field( $_REQUEST['paged'] ) : 1;
	$selected_products = array();
	$per_page          = ( get_option( 'eh_bulk_edit_table_row' ) ) ? get_option( 'eh_bulk_edit_table_row' ) : 20;
	$pid_to_include    = elex_bep_filter_products();

	update_option( 'xa_bulk_selected_ids', $pid_to_include );
	$sel_chunk = array_chunk( $pid_to_include, $per_page, true );
	if ( ! empty( $sel_chunk ) ) {
		$ids_per_page = $sel_chunk[ $page_no - 1 ];
		foreach ( $ids_per_page as $ids ) {
			$selected_products[ $ids ] = wc_get_product( $ids );
		}
	}

	$total_pages = count( $sel_chunk );
	if ( isset( $_REQUEST['page'] ) && ! empty( $table_obj ) && ( $total_pages == 1 ) ) {
		$total_pages++;
	}
	$ele_on_page = count( $pid_to_include );
	if ( ! empty( $table_obj ) ) {
		$table_obj->set_pagination_args(
			array(
				'total_items' => $ele_on_page,
				'per_page'    => $ele_on_page,
				'total_pages' => $total_pages,
			)
		);
	}

	if ( ! empty( $selected_products ) ) {
		return $selected_products;
	}
}

function elex_bep_get_categories( $categories, $subcat ) {
	$filter_categories   = array();
	$selected_categories = $categories;
	$t_arr               = array();
	if ( $subcat ) {
		while ( ! empty( $selected_categories ) ) {
			$slug_name = $selected_categories[0];
			$slug_name = trim( $slug_name, "\'" );
			array_push( $filter_categories, $slug_name );
			unset( $selected_categories[0] );
			$t_arr               = elex_bep_subcats_from_parentcat_by_slug( $slug_name );
			$selected_categories = array_merge( $selected_categories, $t_arr );
		}
	} else {
		foreach ( $categories as $category ) {
			array_push( $filter_categories, $category );
		}
	}
	return $filter_categories;
}

function elex_bep_filter_products( $data = '' ) {
	global $wpdb;
	$prefix = $wpdb->prefix;
	if ( empty( $data ) ) {
		$data_to_filter = $_REQUEST;
	} else {
		$data_to_filter = $data;
	}
	$sql = "SELECT 
                    DISTINCT ID 
                FROM {$prefix}posts 
                    LEFT JOIN {$prefix}term_relationships on {$prefix}term_relationships.object_id={$prefix}posts.ID 
                    LEFT JOIN {$prefix}term_taxonomy on {$prefix}term_taxonomy.term_taxonomy_id  = {$prefix}term_relationships.term_taxonomy_id 
                    LEFT JOIN {$prefix}terms on {$prefix}terms.term_id  ={$prefix}term_taxonomy.term_id 
                    LEFT JOIN {$prefix}postmeta on {$prefix}postmeta.post_id  ={$prefix}posts.ID
                WHERE  post_type = 'product' AND post_status='publish'";

	$title_query = '';
	if ( isset( $data_to_filter['product_title_select'] ) && sanitize_text_field( $data_to_filter['product_title_select'] ) != 'all' && $data_to_filter['product_title_text'] != '' ) {
		switch ( $data_to_filter['product_title_select'] ) {
			case 'starts_with':
				$title_query = " AND post_title LIKE '{$data_to_filter['product_title_text']}%' ";
				break;
			case 'ends_with':
				$title_query = " AND post_title LIKE '%{$data_to_filter['product_title_text']}' ";
				break;
			case 'contains':
				$title_query = " AND post_title LIKE '%{$data_to_filter['product_title_text']}%' ";
				break;
			case 'title_regex':
				$title_query = " AND (post_title REGEXP '{$data_to_filter['product_title_text']}') ";
				break;
		}
	}
	$price_query  = '';
	$filter_range = ! empty( $data_to_filter['range'] ) ? $data_to_filter['range'] : '';
	if ( $filter_range != 'all' && ! empty( $filter_range ) ) {
		if ( $filter_range != '|' ) {
			$price_query = " AND meta_key='_regular_price' AND meta_value {$filter_range} {$data_to_filter['desired_price']} ";
		} else {
			$price_query = " AND meta_key='_regular_price' AND (meta_value >= {$data_to_filter['minimum_price']} AND meta_value <= {$data_to_filter['maximum_price']}) ";
		}
	}

	$attr_condition  = '';
	$attribute_value = '';
	if ( ! empty( $data_to_filter['attribute_value_filter'] ) && is_array( $data_to_filter['attribute_value_filter'] ) ) {
		$attribute_value = implode( ',', $data_to_filter['attribute_value_filter'] );
		$attribute_value = stripslashes( $attribute_value );
		if ( ! empty( $attribute_value ) ) {
			$attr_condition = " CONCAT(taxonomy, ':', slug) IN ({$attribute_value})";
		}
	}
	if ( ! empty( $data_to_filter['attribute_value_and_filter'] ) && is_array( $data_to_filter['attribute_value_and_filter'] ) ) {
		$attribute_value_and = implode( ',', $data_to_filter['attribute_value_and_filter'] );
		$attribute_count = count( $data_to_filter['attribute_value_and_filter']);
		$attribute_value_and = stripslashes( $attribute_value_and );
		if ( !empty( $attribute_value_and ) ){
			$attr_condition = "  {$prefix}posts.ID IN ( SELECT ID FROM {$prefix}posts 
			LEFT JOIN {$prefix}term_relationships on {$prefix}term_relationships.object_id={$prefix}posts.ID 
			LEFT JOIN {$prefix}term_taxonomy on {$prefix}term_taxonomy.term_taxonomy_id  = {$prefix}term_relationships.term_taxonomy_id 
			LEFT JOIN {$prefix}terms on {$prefix}terms.term_id  ={$prefix}term_taxonomy.term_id 
			WHERE  post_type = 'product' AND post_status='publish'
			AND CONCAT(taxonomy,':',slug)  in ({$attribute_value_and}) GROUP BY {$prefix}posts.ID HAVING COUNT( {$prefix}posts.ID) ={$attribute_count}) ";
		}
	}
	$category_condition = '';
	$filter_categories  = array();
	if ( ! empty( $data_to_filter['category_filter'] ) && is_array( $data_to_filter['category_filter'] ) ) {
		$filter_categories = elex_bep_get_categories( $data_to_filter['category_filter'], $data_to_filter['sub_category_filter'] );
		$cat_cond          = '';
		foreach ( $filter_categories as $cats ) {
			if ( empty( $cat_cond ) ) {
				$cat_cond = "'" . $cats . "'";
			} else {
				$cat_cond .= ",'" . $cats . "'";
			}
		}
		$category_condition = " taxonomy='product_cat' AND slug  in ({$cat_cond}) ";
	}
	$exclude_categories = array();
	if ( ! empty( $data_to_filter['exclude_categories'] ) && is_array( $data_to_filter['exclude_categories'] ) ) {
		$exclude_categories = elex_bep_get_categories( $data_to_filter['exclude_categories'], $data_to_filter['exclude_categories'] );
		$cat_cond           = '';
		foreach ( $exclude_categories as $cats ) {
			if ( empty( $cat_cond ) ) {
				$cat_cond = "'" . $cats . "'";
			} else {
				$cat_cond .= ",'" . $cats . "'";
			}
		}
		if ( empty( $category_condition ) ) {
			$category_condition = " taxonomy='product_cat' AND slug NOT in ({$cat_cond}) ";
		} else {
			$category_condition .= " AND taxonomy='product_cat' AND slug NOT in ({$cat_cond}) ";
		}
	}

	if ( ! empty( $title_query ) ) {
		$sql .= $title_query;
	}
	if ( ! empty( $price_query ) ) {
		$sql .= $price_query;
	}

	$ids_simple = array();
	if ( empty( $data_to_filter['type'] ) || in_array( 'simple', $data_to_filter['type'] ) ) {
		$product_type_condition = " taxonomy='product_type'  AND slug  in ('simple') ";

		if ( ! empty( $attr_condition ) && ! empty( $category_condition ) ) {
			$main_query = $sql . ' AND ' . $attr_condition . ' AND ID IN (' . $sql . ' AND ' . $category_condition . ' AND ID IN (' . $sql . ' AND ' . $product_type_condition . '))';
		} elseif ( ! empty( $attr_condition ) && empty( $category_condition ) ) {
			$main_query = $sql . ' AND ' . $attr_condition . ' AND ID IN (' . $sql . ' AND ' . $product_type_condition . ')';
		} elseif ( ! empty( $category_condition ) && empty( $attr_condition ) ) {
			$main_query = $sql . ' AND ' . $category_condition . ' AND ID IN (' . $sql . ' AND ' . $product_type_condition . ')';
		} else {
			$main_query = $sql . ' AND ' . $product_type_condition;
		}
		$result     = $wpdb->get_results( $main_query, ARRAY_A );
		$ids_simple = wp_list_pluck( $result, 'ID' );
	}

	$res_id = $ids_simple;
	if ( isset( $_POST['enable_exclude_prods'] ) && $_POST['enable_exclude_prods'] && ! empty( $res_id ) && ! empty( $data_to_filter['exclude_ids'] ) ) {
		foreach ( $res_id as $key => $val ) {
			if ( in_array( $val, $data_to_filter['exclude_ids'] ) ) {
				unset( $res_id[ $key ] );
			}
		}
	}

	return $res_id;
}

// Get Subcategories
function elex_bep_subcats_from_parentcat_by_slug( $parent_cat_slug ) {
	$ID_by_slug     = get_term_by( 'slug', $parent_cat_slug, 'product_cat' );
	$product_cat_ID = $ID_by_slug->term_id;
	$args           = array(
		'hierarchical'     => 1,
		'show_option_none' => '',
		'hide_empty'       => 0,
		'parent'           => $product_cat_ID,
		'taxonomy'         => 'product_cat',
	);
	$subcats        = get_categories( $args );
	$temp_arr       = array();
	foreach ( $subcats as $sc ) {
		array_push( $temp_arr, $sc->slug );
	}
	return $temp_arr;
}
