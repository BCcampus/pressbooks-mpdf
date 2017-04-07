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

/**
 * MPDF overrides.
 *
 * @param string $scss
 * @return string $scss
 */
function pressbooks_theme_mpdf_css_override( $scss ) {
	$options = get_option( 'pressbooks_theme_options_mpdf' );
	$global_options = get_option( 'pressbooks_theme_options_global' );

	// indent paragraphs
	if ( $options['mpdf_indent_paragraphs'] ) {
		$scss .= "p + p, .indent {text-indent: 2.0 em; }" . "\n";
	}
	// hyphenation
	if ( $options['mpdf_hyphens'] ) {
		$scss .= "p {hyphens: auto;}" . "\n";
	}
	// font-size
	if ( $options['mpdf_fontsize'] ){
                $scss .= 'body {font-size: 1.3em; line-height: 1.3; }' . "\n";
        }
	// chapter numbers
	if ( ! $global_options['chapter_numbers'] ) {
		$scss .= "h3.chapter-number {display: none;}" . "\n";
	}
	return $scss;
}
add_filter( 'pb_mpdf_css_override', 'pressbooks_theme_mpdf_css_override' );
