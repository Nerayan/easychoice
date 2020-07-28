<?php
/**
 * Redux Options Object Extension Class
 *
 * @class Redux_Core
 * @version 4.0.0
 * @package Redux Framework
 */

defined( 'ABSPATH' ) || exit;

// Don't duplicate me!
if ( ! class_exists( 'Redux_Extension_Options_Object', false ) ) {


	/**
	 * Main ReduxFramework options_object extension class
	 *
	 * @since       3.1.6
	 */
	class Redux_Extension_Options_Object extends Redux_Extension_Abstract {

		/**
		 * Ext version.
		 *
		 * @var string
		 */
		public static $version = '4.0';

		/**
		 * Is field bit.
		 *
		 * @var bool
		 */
		public $is_field = false;

		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $parent Redux object.
		 *
		 * @return      void
		 */
		public function __construct( $parent ) {
			parent::__construct( $parent, __FILE__ );

			$this->add_field( 'options_object' );

			$this->is_field = Redux_Helpers::is_field_in_use( $parent, 'options_object' );

			if ( ! $this->is_field && $this->parent->args['dev_mode'] && $this->parent->args['show_options_object'] ) {
				$this->add_section();
			}
		}

		/**
		 * Add sectio to panel.
		 */
		public function add_section() {
			$this->parent->sections[] = array(
				'id'         => 'options-object',
				'title'      => esc_html__( 'Options Object', 'redux-framework' ),
				'heading'    => '',
				'icon'       => 'el el-info-circle',
				'customizer' => false,
				'fields'     => array(
					array(
						'id'    => 'redux_options_object',
						'type'  => 'options_object',
						'title' => '',
					),
				),
			);
		}
	}
}
