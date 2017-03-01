<?php

// Filter export formats array
function pb_mpdf_add_to_formats( $formats ) {
		array_splice( $formats['standard'], 2, 0, array( 'mpdf' => __( 'PDF (mPDF)', 'pressbooks' ) ) );
		return $formats;
}
add_filter( 'pb_export_formats', 'pb_mpdf_add_to_formats' );

// Define active export modules
function pb_mpdf_add_to_modules( $modules ) {
	if ( isset( $_POST['export_formats']['mpdf'] ) ) { // @codingStandardsIgnoreLine
		$modules[] = '\Pressbooks\Modules\Export\Mpdf\Pdf';
	}
	return $modules;
}
add_filter( 'pb_active_export_modules', 'pb_mpdf_add_to_modules' );

// Add theme options
function pb_mpdf_add_theme_options_tab( $tabs ) {
	$tabs['mpdf'] = '\Pressbooks\Modules\ThemeOptions\MPDFOptions';
	return $tabs;
}
add_filter( 'pb_theme_options_tabs', 'pb_mpdf_add_theme_options_tab' );
