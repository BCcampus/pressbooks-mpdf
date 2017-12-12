<?php
/**
 * @author    Brad Payne
 * @license   GPLv2
 * @copyright Brad Payne
 *
 */

add_filter( 'pb_export_formats', function ( $formats ) {
	$formats['standard'] = [ 'mpdf' => __( 'PDF (mPDF)', 'pressbooks-mpdf' ) ] + $formats['standard'];

	return $formats;
} );

add_filter( 'pb_active_export_modules', function ( $modules ) {
	if ( isset( $_POST['export_formats']['mpdf'] ) ) { // @codingStandardsIgnoreLine
		$modules[] = '\BCcampus\Modules\Export\Mpdf\Pdf';
	}

	return $modules;
} );

add_filter( 'pb_theme_options_tabs', function ( $tabs ) {
	$tabs['mpdf'] = '\BCcampus\Modules\ThemeOptions\MpdfOptions';

	return $tabs;
} );

add_filter( 'pb_mpdf_css_override', function ( $scss ) {
	$options        = get_option( 'pressbooks_theme_options_mpdf' );
	$global_options = get_option( 'pressbooks_theme_options_global' );

	// indent paragraphs
	if ( $options['mpdf_indent_paragraphs'] ) {
		$scss .= 'p + p, .indent {text-indent: 2.0 em; }' . "\n";
	}
	// hyphenation
	if ( $options['mpdf_hyphens'] ) {
		$scss .= 'p {hyphens: auto;}' . "\n";
	}
	// font-size
	if ( $options['mpdf_fontsize'] ) {
		$scss .= 'body {font-size: 1.3em; line-height: 1.3; }' . "\n";
	}
	// chapter numbers
	if ( ! $global_options['chapter_numbers'] ) {
		$scss .= 'h3.chapter-number {display: none;}' . "\n";
	}

	return $scss;
} );
