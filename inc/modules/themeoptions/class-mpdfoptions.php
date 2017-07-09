<?php
/**
 * @author  Pressbooks <code@pressbooks.com>
 * @license GPLv2 (or any later version)
 */
namespace Pressbooks\Modules\ThemeOptions;

use Pressbooks\Container;
use Pressbooks\CustomCss;

class MPDFOptions extends \Pressbooks\Options {

	/**
	 * The value for option: pressbooks_theme_options_mpdf_version
	 *
	 * @see upgrade()
	 * @var int
	 */
	const VERSION = 0;

	/**
	* PDF theme options.
	*
	* @var array
	*/
	public $options;

	/**
	* PDF theme defaults.
	*
	* @var array
	*/
	public $defaults;

	/**
	* Constructor.
	*
	* @param array $options
	*/
	function __construct( array $options ) {
		$this->options = $options;
		$this->defaults = $this->getDefaults();
		$this->booleans = $this->getBooleanOptions();
		$this->strings = $this->getStringOptions();
		$this->integers = $this->getIntegerOptions();
		$this->floats = $this->getFloatOptions();
		$this->predefined = $this->getPredefinedOptions();

		foreach ( $this->defaults as $key => $value ) {
			if ( ! isset( $this->options[ $key ] ) ) {
				$this->options[ $key ] = $value;
			}
		}
	}

	/**
	 * Configure the mPDF options tab using the settings API.
	 */
	function init() {
		$_page = $_option = 'pressbooks_theme_options_' . $this->getSlug();
		$_section = $this->getSlug() . '_options_section';

		if ( false === get_option( $_option ) ) {
			add_option( $_option, $this->defaults );
		}

		add_settings_section(
			$_section,
			$this->getTitle(),
			[ $this, 'display' ],
			$_page
		);

		add_settings_field(
			'mpdf_page_size',
			__( 'Page Size', 'pressbooks' ),
			[ $this, 'renderPageSizeField' ],
			$_page,
			$_section,
			[
				'A0' => __( 'A0', 'pressbooks' ),
				'A1' => __( 'A1', 'pressbooks' ),
				'A2' => __( 'A2', 'pressbooks' ),
				'A3' => __( 'A3', 'pressbooks' ),
				'A4' => __( 'A4', 'pressbooks' ),
				'A5' => __( 'A5', 'pressbooks' ),
				'A6' => __( 'A6', 'pressbooks' ),
				'A7' => __( 'A7', 'pressbooks' ),
				'A8' => __( 'A8', 'pressbooks' ),
				'A9' => __( 'A9', 'pressbooks' ),
				'A10' => __( 'A10', 'pressbooks' ),
				'B0' => __( 'B0', 'pressbooks' ),
				'B1' => __( 'B1', 'pressbooks' ),
				'B2' => __( 'B2', 'pressbooks' ),
				'B3' => __( 'B3', 'pressbooks' ),
				'B4' => __( 'B4', 'pressbooks' ),
				'B5' => __( 'B5', 'pressbooks' ),
				'B6' => __( 'B6', 'pressbooks' ),
				'B7' => __( 'B7', 'pressbooks' ),
				'B8' => __( 'B8', 'pressbooks' ),
				'B9' => __( 'B9', 'pressbooks' ),
				'B10' => __( 'B10', 'pressbooks' ),
				'C0' => __( 'C0', 'pressbooks' ),
				'C1' => __( 'C1', 'pressbooks' ),
				'C2' => __( 'C2', 'pressbooks' ),
				'C3' => __( 'C3', 'pressbooks' ),
				'C4' => __( 'C4', 'pressbooks' ),
				'C5' => __( 'C5', 'pressbooks' ),
				'C6' => __( 'C6', 'pressbooks' ),
				'C7' => __( 'C7', 'pressbooks' ),
				'C8' => __( 'C8', 'pressbooks' ),
				'C9' => __( 'C9', 'pressbooks' ),
				'C10' => __( 'C10', 'pressbooks' ),
				'4A0' => __( '4A0', 'pressbooks' ),
				'2A0' => __( '2A0', 'pressbooks' ),
				'RA0' => __( 'RA0', 'pressbooks' ),
				'RA1' => __( 'RA1', 'pressbooks' ),
				'RA2' => __( 'RA2', 'pressbooks' ),
				'RA3' => __( 'RA3', 'pressbooks' ),
				'RA4' => __( 'RA4', 'pressbooks' ),
				'SRA0' => __( 'SRA0', 'pressbooks' ),
				'SRA1' => __( 'SRA1', 'pressbooks' ),
				'SRA2' => __( 'SRA2', 'pressbooks' ),
				'SRA3' => __( 'SRA3', 'pressbooks' ),
				'SRA4' => __( 'SRA4', 'pressbooks' ),
				'Letter' => __( 'Letter', 'pressbooks' ),
				'Legal' => __( 'Legal' , 'pressbooks' ),
				'Executive' => __( 'Executive' , 'pressbooks' ),
				'Folio' => __( 'Folio' , 'pressbooks' ),
				'Demy' => __( 'Demy' , 'pressbooks' ),
				'Royal' => __( 'Royal' , 'pressbooks' ),
				'A' => __( 'Type A paperback 111x178mm' , 'pressbooks' ),
				'B' => __( 'Type B paperback 128x198mm' , 'pressbooks' ),
			]
		);

		add_settings_field(
			'mpdf_margin_left',
			__( 'Left margin', 'pressbooks' ),
			[ $this, 'renderLeftMarginField' ],
			$_page,
			$_section,
			[
				__( 'Left Margin (in millimetres)', 'pressbooks' )
			]
		);

		add_settings_field(
			'mpdf_margin_right',
			__( 'Right margin', 'pressbooks' ),
			[ $this, 'renderRightMarginField' ],
			$_page,
			$_section,
			[
				__( ' Right margin (in milimeters)', 'pressbooks' )
			]
		);

		add_settings_field(
			'mpdf_mirror_margins',
			__( 'Mirror Margins', 'pressbooks' ),
			[ $this, 'renderMirrorMarginsField' ],
			$_page,
			$_section,
			[
				 __( 'The document will mirror the left and right margin values on odd and even pages (i.e. they become inner and outer margins)', 'pressbooks' )
			]
		);

		add_settings_field(
			'mpdf_include_cover',
			__( 'Cover Image', 'pressbooks' ),
			[ $this, 'renderCoverImageField' ],
			$_page,
			$_section,
			[
				 __( 'Display cover image', 'pressbooks' )
			]
		);

		add_settings_field(
			'mpdf_include_toc',
			__( 'Table of Contents', 'pressbooks' ),
			[ $this, 'renderTOCField' ],
			$_page,
			$_section,
			[
				 __( 'Display table of contents', 'pressbooks' )
			]
		);

		add_settings_field(
			'mpdf_indent_paragraphs',
			__( 'Indent paragraphs', 'pressbooks' ),
			[ $this, 'renderIndentParagraphsField' ],
			$_page,
			$_section,
			[
				 __( 'Indent paragraphs', 'pressbooks' )
			]
		);

		add_settings_field(
			'mpdf_hyphens',
			__( 'Hyphens', 'pressbooks' ),
			[ $this, 'renderHyphensField' ],
			$_page,
			$_section,
			[
				 __( 'Enable hyphenation', 'pressbooks' )
			]
		);

		add_settings_field(
			'mpdf_fontsize',
			__( 'Increase Font Size', 'pressbooks' ),
			[ $this, 'renderFontSizeField' ],
			$_page,
			$_section,
			[
				__( 'Increases font size and line height for greater accessibility', 'pressbooks' )
			]
		);

		/**
		 * Add custom settings fields.
		 *
		 * @since 1.6.2
		 */
		do_action( 'pb_theme_options_mpdf_add_settings_fields', $_page, $_section );

		register_setting(
			$_option,
			$_option,
			[ $this, 'sanitize' ]
		);
	}

	/**
	 * Display the mPDF options tab description.
	 */
	function display() {
		echo '<p>' . __( 'These options apply to mPDF exports.', 'pressbooks' ) . '</p>';
	}

	/**
	 * Render the mPDF options tab form (NOT USED).
	 */
	function render() {}

	/**
	 * Upgrade handler for mPDF options (none at present).
	 *
	 * @param int $version
	 */
	function upgrade( $version ) {}

	/**
	 * Render the mpdf_page_size input.
	 * @param array $args
	 */
	function renderPageSizeField( $args ) {
		$this->renderSelect( [
			'id' => 'mpdf_page_size',
			'name' => 'pressbooks_theme_options_' . $this->getSlug(),
			'option' => 'mpdf_page_size',
			'value' => $this->options['mpdf_page_size'],
			'choices' => $args,
		] );
	}

	/**
	 * Render the mpdf_margin_left input.
	 * @param array $args
	 */
	function renderLeftMarginField( $args ) {
		$this->renderField( [
			'id' => 'mpdf_margin_left',
			'name' => 'pressbooks_theme_options_' . $this->getSlug(),
			'option' => 'mpdf_margin_left',
			'value' => $this->options['mpdf_margin_left'],
			'description' => $args[0],
		] );
	}

	/**
	 * Render the mpdf_margin_right input.
	 * @param array $args
	 */
	function renderRightMarginField( $args ) {
		$this->renderField( [
			'id' => 'mpdf_margin_right',
			'name' => 'pressbooks_theme_options_' . $this->getSlug(),
			'option' => 'mpdf_margin_right',
			'value' => $this->options['mpdf_margin_right'],
			'description' => $args[0],
		] );
	}

	/**
	 * Render the mpdf_mirror_margins checkbox.
	 * @param array $args
	 */
	function renderMirrorMarginsField( $args ) {
		$this->renderCheckbox( [
				'id' => 'mpdf_mirror_margins',
				'name' => 'pressbooks_theme_options_' . $this->getSlug(),
				'option' => 'mpdf_mirror_margins',
				'value' => $this->options['mpdf_mirror_margins'],
				'label' => $args[0],
		] );
	}

	/**
	 * Render the mpdf_include_cover checkbox.
	 * @param array $args
	 */
	function renderCoverImageField( $args ) {
		$this->renderCheckbox( [
			'id' => 'mpdf_include_cover',
			'name' => 'pressbooks_theme_options_' . $this->getSlug(),
			'option' => 'mpdf_include_cover',
			'value' => $this->options['mpdf_include_cover'],
			'label' => $args[0],
		] );
	}

	/**
	 * Render the mpdf_include_toc checkbox.
	 * @param array $args
	 */
	function renderTOCField( $args ) {
		$this->renderCheckbox( [
			'id' => 'mpdf_include_toc',
			'name' => 'pressbooks_theme_options_' . $this->getSlug(),
			'option' => 'mpdf_include_toc',
			'value' => $this->options['mpdf_include_toc'],
			'label' => $args[0],
		] );
	}

	/**
	 * Render the mpdf_indent_paragraphs checkbox.
	 * @param array $args
	 */
	function renderIndentParagraphsField( $args ) {
		$this->renderCheckbox( [
			'id' => 'mpdf_indent_paragraphs',
			'name' => 'pressbooks_theme_options_' . $this->getSlug(),
			'option' => 'mpdf_indent_paragraphs',
			'value' => $this->options['mpdf_indent_paragraphs'],
			'label' => $args[0],
		] );
	}

	/**
	 * Render the mpdf_hyphens checkbox.
	 * @param array $args
	 */
	function renderHyphensField( $args ) {
		$this->renderCheckbox( [
			'id' => 'mpdf_hyphens',
			'name' => 'pressbooks_theme_options_' . $this->getSlug(),
			'option' => 'mpdf_hyphens',
			'value' => $this->options['mpdf_hyphens'],
			'label' => $args[0],
		] );
	}

	/**
	 * Render the mpdf_fontsize checkbox.
	 * @param array $args
	 */
	function renderFontSizeField( $args ) {
		$this->renderCheckbox( [
			'id' => 'mpdf_fontsize',
			'name' => 'pressbooks_theme_options_' . $this->getSlug(),
			'option' => 'mpdf_fontsize',
			'value' => $this->options['mpdf_fontsize'],
			'label' => $args[0],
		] );
	}

	/**
	 * Get the slug for the mPDF options tab.
	 *
	 * @return string $slug
	 */
	static function getSlug() {
		return 'mpdf';
	}

	/**
	 * Get the localized title of the mPDF options tab.
	 *
	 * @return string $title
	 */
	static function getTitle() {
		return __( 'mPDF Options', 'pressbooks' );
	}

	/**
	 * Get an array of default values for the mPDF options tab.
	 *
	 * @return array $defaults
	 */
	static function getDefaults() {
		/**
		 * @since 1.6.2 TODO
		 */
		return apply_filters( 'pb_theme_options_mpdf_defaults', [
			'mpdf_page_size' => 'Letter',
			'mpdf_include_cover' => 1,
			'mpdf_indent_paragraphs' => 0,
			'mpdf_include_toc' => 1,
			'mpdf_mirror_margins' => 1,
			'mpdf_margin_left' => 15,
			'mpdf_margin_right' => 30,
			'mpdf_hyphens' => 0,
			'mpdf_fontsize' => 0,
		] );
	}

	/**
	 * Filter the array of default values for the mPDF options tab.
	 *
	 * @param array $defaults
	 * @return array $defaults
	 */
	static function filterDefaults( $defaults ) {
		return $defaults;
	}

	/**
	 * Get an array of options which return booleans.
	 *
	 * @return array $options
	 */
	static function getBooleanOptions() {
		/**
		 * Allow custom boolean options to be passed to sanitization routines.
		 *
		 * @since 1.6.2
		 */
		return apply_filters( 'pb_theme_options_mpdf_booleans', [
			'mpdf_mirror_margins',
			'mpdf_include_cover',
			'mpdf_include_toc',
			'mpdf_indent_paragraphs',
			'mpdf_hyphens',
			'mpdf_fontsize',
		] );
	}

	/**
	 * Get an array of options which return strings.
	 *
	 * @return array $options
	 */
	static function getStringOptions() {
		/**
		 * Allow custom string options to be passed to sanitization routines.
		 *
		 * @since 1.6.2
		 */
		return apply_filters( 'pb_theme_options_mpdf_strings', [] );
	}

	/**
	 * Get an array of options which return integers.
	 *
	 * @return array $options
	 */
	static function getIntegerOptions() {
		/**
		 * Allow custom integer options to be passed to sanitization routines.
		 *
		 * @since 1.6.2
		 */
		return apply_filters( 'pb_theme_options_mpdf_integers', [
			'mpdf_left_margin',
			'mpdf_right_margin',
		] );
	}

	/**
	 * Get an array of options which return floats.
	 *
	 * @return array $options
	 */
	static function getFloatOptions() {
		/**
		 * Allow custom float options to be passed to sanitization routines.
		 *
		 * @since 1.6.2
		 */
		return apply_filters( 'pb_theme_options_mpdf_floats', [] );
	}

	/**
	 * Get an array of options which return predefined values.
	 *
	 * @return array $options
	 */
	static function getPredefinedOptions() {
		/**
		 * Allow custom predifined options to be passed to sanitization routines.
		 *
		 * @since 1.6.2
		 */
		return apply_filters( 'pb_theme_options_mpdf_predefined', [
			'mpdf_page_size'
		] );
	}

	/**
	 * Add format-specific theme options to the theme options page.
	 *
	 * @since 2.0.0
	 * @author Book Oven Inc. <code@pressbooks.com>
	 *
	 * @param array $tabs an array of theme options tabs ('slug' => '\Classname')
	 * @return array $tabs
	 */
	static function addTab( $tabs ) {
		$tabs['mpdf'] = '\Pressbooks\Modules\ThemeOptions\MPDFOptions';
		return $tabs;
	}

	/**
	 * Apply overrides.
	 *
	 * @since 2.0.0
	 * @author Book Oven Inc. <code@pressbooks.com>
	 *
	 * @param string $scss
	 * @return string
	 */
	static function scssOverrides( $scss ) {
		$options = get_option( 'pressbooks_theme_options_mpdf' );
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
	}
}
