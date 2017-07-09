<?php
/**
 * @author    Brad Payne <brad@bradpayne.ca>
 * @license   GPLv2
 * @copyright Brad Payne
 *
 * Plugin Name: Pressbooks mPDF
 * Description:  Open source PDF generation for Pressbooks via the mPDF library.
 * Version: 2.0.0
 * Author: Brad Payne
 * Original Author: BookOven Inc.
 * License: GPLv2
 * Text Domain: pressbooks-mpdf
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Network: True
 */
/**
 *
 * This plugin is forked from the original Pressbooks mPDF https://github.com/pressbooks/pressbooks-mpdf
 * This fork will be maintained by the open source community.
 * Designed to be activated only at the network level.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// -------------------------------------------------------------------------------------------------------------------
// Setup some defaults
// -------------------------------------------------------------------------------------------------------------------

if ( ! defined( 'PB_MPDF_DIR' ) ) {
	define( 'PB_MPDF_DIR', __DIR__ . '/' ); // Must have trailing slash!
}

add_action( 'init', function() {
	// Must meet minimum requirements
	if ( ! @include_once( WP_PLUGIN_DIR . '/pressbooks/compatibility.php' ) ) { // @codingStandardsIgnoreLine
		add_action( 'admin_notices', function () {
			echo '<div id="message" class="error fade"><p>' . __( 'PB mPDF cannot find a Pressbooks install.', 'pressbooks-mpdf' ) . '</p></div>';
		} );
		return;
	} elseif ( ! version_compare( PB_PLUGIN_VERSION, '4.0', '>=' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div id="message" class="error fade"><p>' . __( 'PB mPDF requires Pressbooks 4.0.0 or greater.', 'pressbooks-mpdf' ) . '</p></div>';
		} );
		return;
	} else {
		$wp_upload_dir = wp_upload_dir();
		$tmp_path = $wp_upload_dir['basedir'] . '/mpdf/tmp/';
		$ttffontdata_path = $wp_upload_dir['basedir'] . '/mpdf/ttfontdata/';
		if ( ! file_exists( $tmp_path ) ) {
			mkdir( $tmp_path, 0775, true );
		}
		if ( ! file_exists( $ttffontdata_path ) ) {
			mkdir( $ttffontdata_path, 0775, true );
		}
		define( '_MPDF_TEMP_PATH', $tmp_path );
		define( '_MPDF_TTFONTDATAPATH', $ttffontdata_path );

		\HM\Autoloader\register_class_path( 'Pressbooks', __DIR__ . '/inc' );
		require_once __DIR__ . '/vendor/autoload.php';
		require_once __DIR__ . '/hooks-admin.php';
	}
} );
