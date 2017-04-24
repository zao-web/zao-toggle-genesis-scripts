<?php
/*
 * Plugin Name: Zao Toggle Genesis Scripts Metabox
 * Version: 0.1.0
 * Plugin URI: http://zao.is
 * Description: Toggle which post-types should get the Scripts metabox added to the editor. Requires the CMB2 plugin.
 * Author: Zao
 * Author URI: http://zao.is
 * Requires at least: 4.5
 * Tested up to: 4.7
 *
 * @package WordPress
 * @author Zao
 * @since 0.1.0
 */

define( 'ZTGSM_VERSION', '0.1.0' );
define( 'ZTGSM_DIR', trailingslashit( dirname( __FILE__ ) ) );

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load our settings object when CMB2 loads and init our metabox.
 *
 * @since  0.1.0
 */
function zao_toggle_genesis_scripts_load() {
	require_once( ZTGSM_DIR . 'class-zao-toggle-genesis-scripts.php' );
	Zao_Toggle_Genesis_Scripts::get_instance()->init_metabox();
}
add_action( 'cmb2_admin_init', 'zao_toggle_genesis_scripts_load' );

/**
 * If CMB2 is not loaded, the plugin will not work.
 *
 * @since  0.1.0
 */
function zao_toggle_genesis_scripts_requires_cmb2() {
	if ( ! defined( 'CMB2_LOADED' ) ) {
		echo '<div id="message" class="error"><p>' . __( '"Zao Toggle Genesis Scripts Metabox" requires the CMB2 plugin to be installed/active.', 'zao_toggle_genesis_scripts' ) . '</p></div>';
	}
}
add_action( 'all_admin_notices', 'zao_toggle_genesis_scripts_requires_cmb2' );

/**
 * Load localisation
 * @return void
 */
function zao_toggle_genesis_scripts_load_localisation () {
	load_plugin_textdomain( 'zao_toggle_genesis_scripts', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
add_action( 'init', 'zao_toggle_genesis_scripts_load_localisation', 0 );

/**
 * Load plguin textdomain
 * @return void
 */
function zao_toggle_genesis_scripts_load_plugin_textdomain () {
	$domain = 'zao_toggle_genesis_scripts';

	$locale = apply_filters( 'plugin_locale' , get_locale() , $domain );

	load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'zao_toggle_genesis_scripts_load_plugin_textdomain' );
