<?php
/**
 * Fired during plugin deactivation
 *
 * @link       http://morkva.co.ua/
 * @since      1.0.0
 *
 * @package    morkvajustin-plugin
 * @subpackage morkvajustin-plugin/includes
 */
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    morkvajustin-plugin
 * @subpackage morkvajustin-plugin/includes
 * @author     MORKVA <hello@morkva.co.ua>
 */
class MJS_Plugin_Deactivator {
	/**
	 * The code that runs during plugin deactivation
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
        flush_rewrite_rules();
				//require_once('wos_deactivate.php');
	}
}
