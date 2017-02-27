<?php

// Filter export formats array
function pb_mpdf_add_to_formats( $formats ) {
		unset( $formats['standard']['pdf'] );
		unset( $formats['standard']['print_pdf'] );
		$formats['standard'] = array( 'mpdf' => __( 'PDF (mPDF)', 'pressbooks' ) ) + $formats['standard'];
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
