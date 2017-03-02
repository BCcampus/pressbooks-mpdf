<?php

/**
 * Add this format to the export page formats list.
 *
 * @author Book Oven Inc. <code@pressbooks.com>
 * @param array $formats a multidimensional array of standard and exotic formats
 * @return array $formats
 */
function pb_mpdf_add_to_formats( $formats ) {
		$formats['standard'] = array( 'mpdf' => __( 'PDF (mPDF)', 'pressbooks' ) ) + $formats['standard'];
		return $formats;
}
add_filter( 'pb_export_formats', 'pb_mpdf_add_to_formats' );

/**
 * Add this module to the export batch currently in progress.
 *
 * @author Book Oven Inc. <code@pressbooks.com>
 * @param array $modules an array of active export module classnames
 * @return array $modules
 */
function pb_mpdf_add_to_modules( $modules ) {
	if ( isset( $_POST['export_formats']['mpdf'] ) ) { // @codingStandardsIgnoreLine
		$modules[] = '\Pressbooks\Modules\Export\Mpdf\Pdf';
	}
	return $modules;
}
add_filter( 'pb_active_export_modules', 'pb_mpdf_add_to_modules' );

/**
 * Add format-specific theme options to the theme options page.
 *
 * @author Book Oven Inc. <code@pressbooks.com>
 * @param array $tabs an array of theme options tabs ('slug' => '\Classname')
 * @return array $tabs
 */
function pb_mpdf_add_theme_options_tab( $tabs ) {
	$tabs['mpdf'] = '\Pressbooks\Modules\ThemeOptions\MPDFOptions';
	return $tabs;
}
add_filter( 'pb_theme_options_tabs', 'pb_mpdf_add_theme_options_tab' );
