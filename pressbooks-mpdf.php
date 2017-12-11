<?php
/**
 * @author    Brad Payne <brad@bradpayne.ca>
 * @license   GPLv2
 * @copyright Brad Payne
 *
 * Plugin Name: Pressbooks mPDF
 * Description:  Open source PDF generation for Pressbooks via the mPDF library.
 * Version: 3.0.0
 * Author: Brad Payne
 * Original Author: BookOven Inc.
 * License: GPLv2
 * Text Domain: pressbooks-mpdf
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Network: True
 */
/**
 *
 * This plugin is forked from Pressbooks mPDF https://github.com/pressbooks/pressbooks-mpdf
 * which was based on the original work of Jeff Graham (jgraham909). This fork will be maintained by the open source community.
 * Designed to be activated only at the network level.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// -------------------------------------------------------------------------------------------------------------------
// Setup some defaults
// -------------------------------------------------------------------------------------------------------------------

if ( ! defined( 'PB_MPDF_DIR' ) ) {
	define( 'PB_MPDF_DIR', __DIR__ . '/' ); // Must have trailing slash!
}

add_action( 'init', function () {
	// Must meet minimum requirements
	if ( ! @include_once( WP_PLUGIN_DIR . '/pressbooks/compatibility.php' ) ) { // @codingStandardsIgnoreLine
		add_action( 'admin_notices', function () {
			echo '<div id="message" class="error fade"><p>' . __( 'PB mPDF cannot find a Pressbooks install.', 'pressbooks-mpdf' ) . '</p></div>';
		} );

		return;
	} elseif ( ! version_compare( PB_PLUGIN_VERSION, '4.5', '>=' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div id="message" class="error fade"><p>' . __( 'PB mPDF requires Pressbooks 4.5.0 or greater.', 'pressbooks-mpdf' ) . '</p></div>';
		} );

		return;
	} elseif ( ! function_exists( 'mb_regex_encoding' ) || ! function_exists( 'gd_info' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div id="message" class="error fade"><p>' . __( 'PB mPDF requires the PHP extensions "mbstring" and "gd"', 'pressbooks-mpdf' ) . '</p></div>';
		} );

		return;
	} else {
		$wp_upload_dir    = wp_upload_dir();
		$tmp_path         = $wp_upload_dir['basedir'] . '/mpdf/tmp/';
		$ttffontdata_path = $wp_upload_dir['basedir'] . '/mpdf/ttfontdata/';
		if ( ! file_exists( $tmp_path ) ) {
			mkdir( $tmp_path, 0775, true );
		}
		if ( ! file_exists( $ttffontdata_path ) ) {
			mkdir( $ttffontdata_path, 0775, true );
		}
		define( '_MPDF_TEMP_PATH', $tmp_path );
		define( '_MPDF_TTFONTDATAPATH', $ttffontdata_path );

		require_once __DIR__ . '/autoloader.php';
		require_once __DIR__ . '/vendor/autoload.php';
		require_once __DIR__ . '/hooks-admin.php';
	}
} );
