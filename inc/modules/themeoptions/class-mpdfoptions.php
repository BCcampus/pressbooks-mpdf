<?php
/**
 * @author  Pressbooks <code@pressbooks.com>
 * @license GPLv2 (or any later version)
 */

namespace BCcampus\Modules\ThemeOptions;

use Pressbooks;

class MPDFOptions extends Pressbooks\Options {

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
		$this->options    = $options;
		$this->defaults   = $this->getDefaults();
		$this->booleans   = $this->getBooleanOptions();
		$this->strings    = $this->getStringOptions();
		$this->integers   = $this->getIntegerOptions();
		$this->floats     = $this->getFloatOptions();
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
		$_page    = 'pressbooks_theme_options_' . $this->getSlug();
		$_option  = 'pressbooks_theme_options_' . $this->getSlug();
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
			__( 'Page Size', 'pressbooks-mpdf' ),
			[ $this, 'renderPageSizeField' ],
			$_page,
			$_section,
			[
				'A0'        => __( 'A0 - 841 x 1189mm (33.1 x 46.8 in)', 'pressbooks-mpdf' ),
				'A1'        => __( 'A1 - 594 x 841mm (23.4 x 33.1 in)', 'pressbooks-mpdf' ),
				'A2'        => __( 'A2 - 420 x 594mm (16.5 x 23.4 in)', 'pressbooks-mpdf' ),
				'A3'        => __( 'A3 - 297 x 420mm (11.7 x 16.5 in)', 'pressbooks-mpdf' ),
				'A4'        => __( 'A4 - 210 x 297mm (8.3 x 11.7 in)', 'pressbooks-mpdf' ),
				'A5'        => __( 'A5 - 148 x 210mm (5.8 x 8.3 in)', 'pressbooks-mpdf' ),
				'A6'        => __( 'A6 - 105 x 148mm (4.1 x 5.8 in)', 'pressbooks-mpdf' ),
				'A7'        => __( 'A7 - 74 x 105mm (2.9 x 4.1 in)', 'pressbooks-mpdf' ),
				'A8'        => __( 'A8 - 52 x 74mm (2.0 x 2.9 in)', 'pressbooks-mpdf' ),
				'A9'        => __( 'A9 - 37 x 52mm (1.5 x 2.0 in)', 'pressbooks-mpdf' ),
				'A10'       => __( 'A10 - 26 x 37mm (1.0 x 1.5 in)', 'pressbooks-mpdf' ),
				'B0'        => __( 'B0 - 1000 x 1414mm (39.4 x 55.7 in)', 'pressbooks-mpdf' ),
				'B1'        => __( 'B1 - 707 x 1000mm (27.8 x 39.4 in)', 'pressbooks-mpdf' ),
				'B2'        => __( 'B2 - 500 x 707mm (19.7 x 27.8 in)', 'pressbooks-mpdf' ),
				'B3'        => __( 'B3 - 353 x 500mm (13.9 x 19.7 in)', 'pressbooks-mpdf' ),
				'B4'        => __( 'B4 - 250 x 353mm (9.8 x 13.9 in)', 'pressbooks-mpdf' ),
				'B5'        => __( 'B5 - 176 x 250mm (6.9 x 9.8 in)', 'pressbooks-mpdf' ),
				'B6'        => __( 'B6 - 125 x 176mm (4.9 x 6.9 in)', 'pressbooks-mpdf' ),
				'B7'        => __( 'B7 - 88 x 125mm (3.5 x 4.9 in)', 'pressbooks-mpdf' ),
				'B8'        => __( 'B8 - 62 x 88mm (2.4 x 3.5 in)', 'pressbooks-mpdf' ),
				'B9'        => __( 'B9 - 44 x 62mm (1.7 x 2.4 in)', 'pressbooks-mpdf' ),
				'B10'       => __( 'B10 - 31 x 44mm (1.2 x 1.7 in)', 'pressbooks-mpdf' ),
				'C0'        => __( 'C0 - 917 x 1297mm (36.1 x 51.5 in)', 'pressbooks-mpdf' ),
				'C1'        => __( 'C1 - 648 x 917mm (25.5 x 36.1 in)', 'pressbooks-mpdf' ),
				'C2'        => __( 'C2 - 458 x 648mm (18.0 x 25.5 in)', 'pressbooks-mpdf' ),
				'C3'        => __( 'C3 - 324 x 458mm (12.8 x 18.0 in)', 'pressbooks-mpdf' ),
				'C4'        => __( 'C4 - 229 x 324mm (9.0 x 12.8 in)', 'pressbooks-mpdf' ),
				'C5'        => __( 'C5 - 162 x 229mm (6.4 x 9.0 in)', 'pressbooks-mpdf' ),
				'C6'        => __( 'C6 - 114 x 162mm (4.5 x 6.4 in)', 'pressbooks-mpdf' ),
				'C7'        => __( 'C7 - 81 x 114mm (3.2 x 4.5 in)', 'pressbooks-mpdf' ),
				'C8'        => __( 'C8 - 57 x 81mm (2.2 x 3.2 in)', 'pressbooks-mpdf' ),
				'C9'        => __( 'C9 - 40 x 57mm (1.6 x 2.2 in)', 'pressbooks-mpdf' ),
				'C10'       => __( 'C10 - 28 x 40mm (1.1 x 1.6 in)', 'pressbooks-mpdf' ),
				'4A0'       => __( '4A0 - 1682 x 2378mm (66.2 x 93.6 in)', 'pressbooks-mpdf' ),
				'2A0'       => __( '2A0 - 1189 x 1682mm (46.8 x 66.2 in)', 'pressbooks-mpdf' ),
				'RA0'       => __( 'RA0 - 860 x 1220mm (33.9 x 48.0 in)', 'pressbooks-mpdf' ),
				'RA1'       => __( 'RA - 1610 x 860mm (24.0 x 33.9 in)', 'pressbooks-mpdf' ),
				'RA2'       => __( 'RA2 - 430 x 610mm (16.9 x 24.0 in)', 'pressbooks-mpdf' ),
				'RA3'       => __( 'RA3 - 305 x 430mm (12.0 x 16.9 in)', 'pressbooks-mpdf' ),
				'RA4'       => __( 'RA4 - 215 x 305mm (8.5 x 12.0 in)', 'pressbooks-mpdf' ),
				'SRA0'      => __( 'SRA0 - 900 x 1280mm (35.4 x 50.4 in)', 'pressbooks-mpdf' ),
				'SRA1'      => __( 'SRA1 - 640 x 900 mm (25.2 x 35.4 in)', 'pressbooks-mpdf' ),
				'SRA2'      => __( 'SRA2 - 450 x 640mm (17.7 x 25.2 in)', 'pressbooks-mpdf' ),
				'SRA3'      => __( 'SRA3 - 320 x 450mm (12.6 x 17.7 in)', 'pressbooks-mpdf' ),
				'SRA4'      => __( 'SRA4 - 225 x 320mm (8.9 x 12.6 in)', 'pressbooks-mpdf' ),
				'Letter'    => __( 'Letter - 216 x 279mm	(8.5 x 11.0 in)', 'pressbooks-mpdf' ),
				'Legal'     => __( 'Legal - 216 x 356mm (8.5 x 14.0 in)', 'pressbooks-mpdf' ),
				'Executive' => __( 'Executive - 184.2 x 266.7mm (7.0 x 10.0 in)', 'pressbooks-mpdf' ),
				'Folio'     => __( 'Folio - 210 x 330mm (8.0 x 13.0 in)', 'pressbooks-mpdf' ),
				'Demy'      => __( 'Demy - 450.9 x 571.5mm (17.75 x 22.5 in)', 'pressbooks-mpdf' ),
				'Royal'     => __( 'Royal - 508.0 x 635.0mm (20.0 x 25.0 in)', 'pressbooks-mpdf' ),
				'A'         => __( 'Type A - paperback 111 x 178mm', 'pressbooks-mpdf' ),
				'B'         => __( 'Type B - paperback 128 x 198mm', 'pressbooks-mpdf' ),
			]
		);

		add_settings_field(
			'mpdf_margin_left',
			__( 'Left margin', 'pressbooks-mpdf' ),
			[ $this, 'renderLeftMarginField' ],
			$_page,
			$_section,
			[
				__( 'Left Margin (in millimetres)', 'pressbooks-mpdf' ),
			]
		);

		add_settings_field(
			'mpdf_margin_right',
			__( 'Right margin', 'pressbooks-mpdf' ),
			[ $this, 'renderRightMarginField' ],
			$_page,
			$_section,
			[
				__( ' Right margin (in milimeters)', 'pressbooks-mpdf' ),
			]
		);

		add_settings_field(
			'mpdf_mirror_margins',
			__( 'Mirror Margins', 'pressbooks-mpdf' ),
			[ $this, 'renderMirrorMarginsField' ],
			$_page,
			$_section,
			[
				__( 'The document will mirror the left and right margin values on odd and even pages (i.e. they become inner and outer margins)', 'pressbooks-mpdf' ),
			]
		);

		add_settings_field(
			'mpdf_include_cover',
			__( 'Cover Image', 'pressbooks-mpdf' ),
			[ $this, 'renderCoverImageField' ],
			$_page,
			$_section,
			[
				__( 'Display cover image', 'pressbooks-mpdf' ),
			]
		);

		add_settings_field(
			'mpdf_include_toc',
			__( 'Table of Contents', 'pressbooks-mpdf' ),
			[ $this, 'renderTOCField' ],
			$_page,
			$_section,
			[
				__( 'Display table of contents', 'pressbooks-mpdf' ),
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
		echo '<p>' . __( 'These options apply to mPDF exports.', 'pressbooks-mpdf' ) . '</p>';
	}

	/**
	 * Render the mPDF options tab form (NOT USED).
	 */
	function render() {
	}

	/**
	 * Upgrade handler for mPDF options (none at present).
	 *
	 * @param int $version
	 */
	function upgrade( $version ) {
	}

	/**
	 * Render the mpdf_page_size input.
	 *
	 * @param array $args
	 */
	function renderPageSizeField( $args ) {
		$this->renderSelect(
			[
				'id'      => 'mpdf_page_size',
				'name'    => 'pressbooks_theme_options_' . $this->getSlug(),
				'option'  => 'mpdf_page_size',
				'value'   => $this->options['mpdf_page_size'],
				'choices' => $args,
			]
		);
	}

	/**
	 * Render the mpdf_margin_left input.
	 *
	 * @param array $args
	 */
	function renderLeftMarginField( $args ) {
		$this->renderField(
			[
				'id'          => 'mpdf_margin_left',
				'name'        => 'pressbooks_theme_options_' . $this->getSlug(),
				'option'      => 'mpdf_margin_left',
				'value'       => $this->options['mpdf_margin_left'],
				'description' => $args[0],
			]
		);
	}

	/**
	 * Render the mpdf_margin_right input.
	 *
	 * @param array $args
	 */
	function renderRightMarginField( $args ) {
		$this->renderField(
			[
				'id'          => 'mpdf_margin_right',
				'name'        => 'pressbooks_theme_options_' . $this->getSlug(),
				'option'      => 'mpdf_margin_right',
				'value'       => $this->options['mpdf_margin_right'],
				'description' => $args[0],
			]
		);
	}

	/**
	 * Render the mpdf_mirror_margins checkbox.
	 *
	 * @param array $args
	 */
	function renderMirrorMarginsField( $args ) {
		$this->renderCheckbox(
			[
				'id'     => 'mpdf_mirror_margins',
				'name'   => 'pressbooks_theme_options_' . $this->getSlug(),
				'option' => 'mpdf_mirror_margins',
				'value'  => $this->options['mpdf_mirror_margins'],
				'label'  => $args[0],
			]
		);
	}

	/**
	 * Render the mpdf_include_cover checkbox.
	 *
	 * @param array $args
	 */
	function renderCoverImageField( $args ) {
		$this->renderCheckbox(
			[
				'id'     => 'mpdf_include_cover',
				'name'   => 'pressbooks_theme_options_' . $this->getSlug(),
				'option' => 'mpdf_include_cover',
				'value'  => $this->options['mpdf_include_cover'],
				'label'  => $args[0],
			]
		);
	}

	/**
	 * Render the mpdf_include_toc checkbox.
	 *
	 * @param array $args
	 */
	function renderTOCField( $args ) {
		$this->renderCheckbox(
			[
				'id'     => 'mpdf_include_toc',
				'name'   => 'pressbooks_theme_options_' . $this->getSlug(),
				'option' => 'mpdf_include_toc',
				'value'  => $this->options['mpdf_include_toc'],
				'label'  => $args[0],
			]
		);
	}

	/**
	 * Render the mpdf_indent_paragraphs checkbox.
	 *
	 * @param array $args
	 */
	function renderIndentParagraphsField( $args ) {
		$this->renderCheckbox(
			[
				'id'     => 'mpdf_indent_paragraphs',
				'name'   => 'pressbooks_theme_options_' . $this->getSlug(),
				'option' => 'mpdf_indent_paragraphs',
				'value'  => $this->options['mpdf_indent_paragraphs'],
				'label'  => $args[0],
			]
		);
	}

	/**
	 * Render the mpdf_hyphens checkbox.
	 *
	 * @param array $args
	 */
	function renderHyphensField( $args ) {
		$this->renderCheckbox(
			[
				'id'     => 'mpdf_hyphens',
				'name'   => 'pressbooks_theme_options_' . $this->getSlug(),
				'option' => 'mpdf_hyphens',
				'value'  => $this->options['mpdf_hyphens'],
				'label'  => $args[0],
			]
		);
	}

	/**
	 * Render the mpdf_fontsize checkbox.
	 *
	 * @param array $args
	 */
	function renderFontSizeField( $args ) {
		$this->renderCheckbox(
			[
				'id'     => 'mpdf_fontsize',
				'name'   => 'pressbooks_theme_options_' . $this->getSlug(),
				'option' => 'mpdf_fontsize',
				'value'  => $this->options['mpdf_fontsize'],
				'label'  => $args[0],
			]
		);
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
		return __( 'mPDF Options', 'pressbooks-mpdf' );
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
		return apply_filters(
			'pb_theme_options_mpdf_defaults', [
				'mpdf_page_size'         => 'Letter',
				'mpdf_include_cover'     => 1,
				'mpdf_indent_paragraphs' => 0,
				'mpdf_include_toc'       => 1,
				'mpdf_mirror_margins'    => 1,
				'mpdf_margin_left'       => 15,
				'mpdf_margin_right'      => 30,
				'mpdf_hyphens'           => 0,
				'mpdf_fontsize'          => 0,
			]
		);
	}

	/**
	 * Filter the array of default values for the mPDF options tab.
	 *
	 * @param array $defaults
	 *
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
		return apply_filters(
			'pb_theme_options_mpdf_booleans', [
				'mpdf_mirror_margins',
				'mpdf_include_cover',
				'mpdf_include_toc',
				'mpdf_indent_paragraphs',
				'mpdf_hyphens',
				'mpdf_fontsize',
			]
		);
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
		return apply_filters(
			'pb_theme_options_mpdf_integers', [
				'mpdf_margin_left',
				'mpdf_margin_right',
			]
		);
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
		return apply_filters(
			'pb_theme_options_mpdf_predefined', [
				'mpdf_page_size',
			]
		);
	}

}
