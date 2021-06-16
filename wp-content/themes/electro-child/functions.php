<?php
/**
 * Electro Child
 *
 * @package electro-child
 */

/**
 * Include all your custom code here
 */

add_action( 'woocommerce_single_product_summary', 'dev_designs_show_sku', 5 );
function dev_designs_show_sku(){
    global $product;
    ?>
    <div class="product-sku"> 
    <?php 
    echo 'Код товара: 7' . $product->get_sku();
    ?>
    </div>
    <?php
}

// WP STORE LOCATOR OPENING HOURS
add_filter( 'wpsl_listing_template', 'custom_listing_template' );

function custom_listing_template() {

    global $wpsl, $wpsl_settings;
	
    
	$listing_template = '<div class="wpsl-store-location-head">' . "\r\n";
	$listing_template = '<div>' . "\r\n";
	$listing_template = 'Фото' . "\r\n";
	$listing_template = '</div>' . "\r\n";
	$listing_template = '<div>' . "\r\n";
	$listing_template = 'Название магазина' . "\r\n";
	$listing_template = '</div>' . "\r\n";
	$listing_template = '<div>' . "\r\n";
	$listing_template = 'Адрес' . "\r\n";
	$listing_template = '</div>' . "\r\n";
	$listing_template = '<div>' . "\r\n";
	$listing_template = 'Часы работы' . "\r\n";
	$listing_template = '</div>' . "\r\n";
	$listing_template = '</div>' . "\r\n";
    $listing_template = '<li data-store-id="<%= id %>">' . "\r\n";
    $listing_template .= "\t\t" . '<div class="wpsl-store-location">' . "\r\n";
    $listing_template .= "\t\t\t" . '<p class="wpsl-location"><%= thumb %>' . "\r\n";
    $listing_template .= "\t\t\t\t" . wpsl_store_header_template( 'listing' ) . "\r\n"; // Check which header format we use
    $listing_template .= "\t\t\t\t" . '<span class="wpsl-city">' . wpsl_address_format_placeholders() . '</span>' . "\r\n"; // Use the correct address format
	$listing_template .= "\t\t\t\t" . '<span class="wpsl-street"><%= address %></span>' . "\r\n";
    $listing_template .= "\t\t\t\t" . '<% if ( address2 ) { %>' . "\r\n";
    $listing_template .= "\t\t\t\t" . '<span class="wpsl-street"><%= address2 %></span>' . "\r\n";
    $listing_template .= "\t\t\t\t" . '<% } %>' . "\r\n";
    

    if ( !$wpsl_settings['hide_country'] ) {
        $listing_template .= "\t\t\t\t" . '<span class="wpsl-country"><%= country %></span>' . "\r\n";
    }

    $listing_template .= "\t\t\t" . '</p>' . "\r\n";
    
    // Include the opening hours, unless they are set to hidden on the settings page.
    if ( !$wpsl_settings['hide_hours'] ) {
		$listing_template .= "\t\t\t" . '<div class="opening-hours">' . "\r\n";
			$listing_template .= "\t\t\t" . '<span class="hours-head">' . "\r\n";
			$listing_template .= "\t\t\t" . 'Часы работы' . "\r\n";
			$listing_template .= "\t\t\t" . '</span>' . "\r\n";
			$listing_template .= "\t\t\t" . '<div class="hours-list">' . "\r\n";
				$listing_template .= "\t\t\t" . '<% if ( hours ) { %>' . "\r\n";
				$listing_template .= "\t\t\t" . '<p><%= hours %></p>' . "\r\n";
				$listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
			$listing_template .= "\t\t\t" . '</div>' . "\r\n";
		$listing_template .= "\t\t\t" . '</div>' . "\r\n";
    }

    // Show the phone, fax or email data if they exist.
    if ( $wpsl_settings['show_contact_details'] ) {
        $listing_template .= "\t\t\t" . '<p class="wpsl-contact-details">' . "\r\n";
        $listing_template .= "\t\t\t" . '<% if ( phone ) { %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<span><strong>' . esc_html( $wpsl->i18n->get_translation( 'phone_label', __( 'Phone', 'wpsl' ) ) ) . '</strong>: <%= formatPhoneNumber( phone ) %></span>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% if ( fax ) { %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<span><strong>' . esc_html( $wpsl->i18n->get_translation( 'fax_label', __( 'Fax', 'wpsl' ) ) ) . '</strong>: <%= fax %></span>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% if ( email ) { %>' . "\r\n";
        $listing_template .= "\t\t\t" . '<span><strong>' . esc_html( $wpsl->i18n->get_translation( 'email_label', __( 'Email', 'wpsl' ) ) ) . '</strong>: <%= email %></span>' . "\r\n";
        $listing_template .= "\t\t\t" . '<% } %>' . "\r\n";
        $listing_template .= "\t\t\t" . '</p>' . "\r\n";
    }

    $listing_template .= "\t\t\t" . wpsl_more_info_template() . "\r\n"; // Check if we need to show the 'More Info' link and info
    $listing_template .= "\t\t" . '</div>' . "\r\n";
    $listing_template .= "\t\t" . '<div class="wpsl-direction-wrap">' . "\r\n";

    if ( !$wpsl_settings['hide_distance'] ) {
        $listing_template .= "\t\t\t" . '<%= distance %> ' . esc_html( $wpsl_settings['distance_unit'] ) . '' . "\r\n";
    }

    $listing_template .= "\t\t\t" . '<%= createDirectionUrl() %>' . "\r\n"; 
    $listing_template .= "\t\t" . '</div>' . "\r\n";
    $listing_template .= "\t" . '</li>';

    return $listing_template;
}

// VIEW HISTORY IN HEADER

function wpb_widgets_init() {
 
    register_sidebar( array(
        'name'          => 'View History Header Widget',
        'id'            => 'view-history-header-widget',
        'before_widget' => '<div class="view-history-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="view-history-title">',
        'after_title'   => '</h2>',
    ) );
 
}
add_action( 'widgets_init', 'wpb_widgets_init' );

if ( ! function_exists( 'electro_header_icons' ) ) {
    /**
     * @since 2.0
     */
    function electro_header_icons() {
        ?><div class="header-icons">
        <div class="header-icon view-history"><i class="far fa-eye"></i>
            <?php
             
            if ( is_active_sidebar( 'view-history-header-widget' ) ) : ?>
                <div id="header-widget-area" class="vh-widget widget-area" role="complementary">
                <?php dynamic_sidebar( 'view-history-header-widget' ); ?>
                </div>
                 
            <?php endif; ?>
        </div>
        <?php
        /**
         *
         */
        do_action( 'electro_header_icons' ); ?></div><!-- /.header-icons --><?php
    }
}

// ATTRIBUTE TABLE OPEN
function startTableAttribute() {
	echo '<table class="custom-attributes"><tbody>';
}
add_action('woocommerce_after_single_product_summary', 'startTableAttribute', 10);

function productModel() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_sovmestimayamodel');
	$attribute_name = "pa_sovmestimayamodel";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function caseSize() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_razmer-korpusa');
	$attribute_name = "pa_razmer-korpusa";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function manufacturer() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_proizvoditel');
	$attribute_name = "pa_proizvoditel";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function memorySize() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_obyom-pamyati');
	$attribute_name = "pa_obyom-pamyati";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function sovmestimyyBrand() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_sovmestimyybrend');
	$attribute_name = "pa_sovmestimyybrend";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function formFactor() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_formfactor');
	$attribute_name = "pa_formfactor";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function kleevoySloy() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_kleyevoy-sloy');
	$attribute_name = "pa_kleyevoy-sloy";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function material() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_material');
	$attribute_name = "pa_material";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function nalichiyeRamki() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_nalichiye-ramki');
	$attribute_name = "pa_nalichiye-ramki";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function tip() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_tip');
	$attribute_name = "pa_tip";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function vid() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_vid');
	$attribute_name = "pa_vid";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function dlina() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_dlina');
	$attribute_name = "pa_dlina";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function сvet() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_сvet');
	$attribute_name = "pa_сvet";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function garantia() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_garantia');
	$attribute_name = "pa_garantia";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function stranaProizvoditel() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_stranaproizvoditeltovara');
	$attribute_name = "pa_stranaproizvoditeltovara";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}
function stranaRegistratsiiBrenda() {
	global $product;
	$attribute_names = get_the_terms($product->get_id(), 'pa_stranaregistratsiibrenda');
	$attribute_name = "pa_stranaregistratsiibrenda";
	if ($attribute_names) {
		echo '<tr><td><span>' . wc_attribute_label($attribute_name) . '</span> </td>';
		foreach ($attribute_names as $attribute_name):
			echo '<td>' . $attribute_name->name . '</td></tr>';
		endforeach;
	}
}

// Определяем место вывода атрибута
// add_action('woocommerce_after_single_product_summary', 'manufacturer', 10);
// add_action('woocommerce_after_single_product_summary', 'sovmestimyyBrand', 10);
// add_action('woocommerce_after_single_product_summary', 'productModel', 10);
// add_action('woocommerce_after_single_product_summary', 'caseSize', 10);
// add_action('woocommerce_after_single_product_summary', 'memorySize', 10);
// add_action('woocommerce_after_single_product_summary', 'formFactor', 10);
// add_action('woocommerce_after_single_product_summary', 'kleevoySloy', 10);
// add_action('woocommerce_after_single_product_summary', 'material', 10);
// add_action('woocommerce_after_single_product_summary', 'nalichiyeRamki', 10);
// add_action('woocommerce_after_single_product_summary', 'tip', 10);
// add_action('woocommerce_after_single_product_summary', 'vid', 10);
// add_action('woocommerce_after_single_product_summary', 'dlina', 10);
// add_action('woocommerce_after_single_product_summary', 'сvet', 10);
// add_action('woocommerce_after_single_product_summary', 'garantia', 10);
// add_action('woocommerce_after_single_product_summary', 'stranaProizvoditel', 10);
// add_action('woocommerce_after_single_product_summary', 'stranaregistratsiibrenda', 10);

// Закрываем таблицу для атрибутов
function endTableAttribute() {
	echo '</tbody></table>';
}
add_action('woocommerce_after_single_product_summary', 'endTableAttribute', 10);

// dont display products without image
add_action( 'woocommerce_product_query', 'custom_pre_get_posts_query' );
function custom_pre_get_posts_query( $query ) {

    $query->set( 'meta_query', array( array(
       'key' => '_thumbnail_id',
       'value' => '0',
       'compare' => '>'
    )));

}

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 40 );
