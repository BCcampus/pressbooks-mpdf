<?php
/**
 * @author    Brad Payne <brad@bradpayne.ca>
 * @license   GPLv2
 * @copyright Brad Payne
 *
 * Plugin Name: Pressbooks mPDF
 * Description:  Open source PDF generation for Pressbooks via the mPDF library.
 * Version: 1.6.1
 * Author: Brad Payne
 * Original Author: BookOven Inc.
 * License: GPLv2
 * Text Domain: pressbooks-mpdf
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
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
	define( 'PB_MPDF_DIR', __DIR__ . '/' );
} // Must have trailing slash!

// -------------------------------------------------------------------------------------------------------------------
// Check mpdf export paths
// -------------------------------------------------------------------------------------------------------------------

add_action( 'admin_notices', function () {
	$paths = array(
		PB_MPDF_DIR . 'vendor/mpdf/mpdf/ttfontdata',
		PB_MPDF_DIR . 'vendor/mpdf/mpdf/tmp',
		PB_MPDF_DIR . 'vendor/mpdf/mpdf/graph_cache',
	);

	foreach ( $paths as $path ) {
		// try making them writeable first
		chmod( $path, 0775 );
		// alert for server admin intervention
		if ( ! is_writable( $path ) ) {
			$_SESSION['pb_errors'][] = sprintf( __( 'The path "%s" is not writable. Please check and adjust the ownership and file permissions for mpdf export to work properly.', 'pressbooks-mpdf' ), $path );
		}
	}
} );

// Must meet miniumum requirements

if ( ! @include_once( WP_PLUGIN_DIR . '/pressbooks/compatibility.php' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div id="message" class="error fade"><p>' . __( 'PB mPDF cannot find a Pressbooks install.', 'pressbooks-mpdf' ) . '</p></div>';
	} );

	return;

} else {
	require_once PB_MPDF_DIR . 'vendor/autoload.php';
}