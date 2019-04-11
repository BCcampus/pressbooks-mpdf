<?php
/**
 * Plugin Name: mPDF for Pressbooks
 * Description:  Open source PDF generation for Pressbooks via the mPDF library.
 * Version: 3.3.1
 * Author: BCcampus
 * Author URI: https://github.com/bccampus/
 * License: GPLv2
 * Text Domain: pressbooks-mpdf
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Network: True
 * Tags: pressbooks, OER, publishing, PDF, export
 * Pressbooks tested up to: 5.7.0
 */

/**
 *
 * This plugin is forked from Pressbooks mPDF
 * https://github.com/pressbooks/pressbooks-mpdf which was based on the
 * original work of Jeff Graham (jgraham909). This fork will be maintained by
 * the open source community. Designed to be activated only at the network
 * level.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

add_action( 'init', function () {
	// Must meet minimum requirements
	if ( ! @include_once( WP_PLUGIN_DIR . '/pressbooks/compatibility.php' ) ) { // phpcs:ignore
		add_action( 'admin_notices', function () {
			echo '<div id="message" class="error fade"><p>' . __( 'mPDF for Pressbooks cannot find a Pressbooks install.', 'pressbooks-mpdf' ) . '</p></div>';
		} );

		return;
	} elseif ( ! version_compare( PB_PLUGIN_VERSION, '5.0.0', '>=' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div id="message" class="error fade"><p>' . __( 'mPDF for Pressbooks requires Pressbooks 5.0.0 or greater.', 'pressbooks-mpdf' ) . '</p></div>';
		} );

		return;
	} elseif ( ! function_exists( 'mb_regex_encoding' ) || ! function_exists( 'gd_info' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div id="message" class="error fade"><p>' . __( 'mPDF for Pressbooks requires the PHP extensions "mbstring" and "gd"', 'pressbooks-mpdf' ) . '</p></div>';
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
		// phpcs:disable
		define( '_MPDF_TEMP_PATH', $tmp_path );
		define( '_MPDF_TTFONTDATAPATH', $ttffontdata_path );
		// phpcs:enable

		require_once __DIR__ . '/autoloader.php';

		// Load Composer Dependencies
		$composer = __DIR__ . '/vendor/autoload.php';
		if ( file_exists( $composer ) ) {
			require_once( $composer );
		}
		require_once __DIR__ . '/hooks-admin.php';
	}
} );
