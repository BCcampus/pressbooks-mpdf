<?php

/**
 * @author  Pressbooks <code@pressbooks.com>
 * @license GPLv2 (or any later version))
 */

namespace BCcampus\Modules\Export\Mpdf;

/**
 * Available filters
 *
 * Overrides the Table of Contents entry;
 *
 *     function my_mpdf_get_toc_entry( $value ) {
 *       return sprintf(__('Chapter: %s'), $value['post_title']);
 *     }
 *     add_filter( 'mpdf_get_toc_entry', 'my_mpdf_get_toc_entry', 10, 1 );
 *
 * Overrides the footer ;
 *
 *     function my_mpdf_footer( $content ) {
 *       return 'left content | center content | {PAGENO}';
 *     }
 *     add_filter( 'mpdf_get_footer', 'my_mpdf_footer', 10, 1 );
 *
 * Overrides the header;
 *
 *     function my_mpdf_header( $content ) {
 *       return 'left content | center content | {PAGENO}';
 *     }
 *     add_filter( 'mpdf_get_header', 'my_mpdf_header', 10, 1 );
 *
 */

use Masterminds\HTML5;
use Mpdf\Mpdf;
use Pressbooks\Book;
use Pressbooks\Sanitize;
use Pressbooks\Modules\Export\Export;
use Pressbooks\Taxonomy;

class Pdf extends Export {

	/**
	 * Fullpath to book CSS file.
	 *
	 * @var string
	 */
	protected $exportStylePath;

	/**
	 * mPDF theme options, set by the user
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Global theme options, set by the user
	 *
	 * @var array
	 */
	protected $globalOptions;

	/**
	 * MPDF Class
	 *
	 * @var object
	 */
	protected $mpdf;

	/**
	 * mPDF uses a lot of memory, this the recommended minimum
	 *
	 * @see http://mpdf1.com/manual/index.php?tid=408
	 * @var int
	 */
	protected $memoryNeeded = 128;

	/**
	 * Holds the title of the book being published
	 * @var string
	 */
	protected $bookTitle;

	/**
	 * Book Metadata
	 *
	 * @var array
	 */
	protected $bookMeta;

	/**
	 * Number the chapters
	 *
	 * @var boolean
	 */
	protected $numbered = false;

	/**
	 * Parses the html as styles and stylesheets only
	 * @see http://mpdf1.com/manual/index.php?tid=121
	 *
	 */
	const MODE_CSS = 1;

	/**
	 * endpoint for xhtml page
	 * @var string
	 */
	public $url;

	/**
	 *
	 */
	function __construct() {
		// don't know who would want to wait for 20 minutes, but it's here
		if ( ! ini_get( 'safe_mode' ) ) {
			$time_limit = (int) ini_get( 'max_execution_time' );
			if ( $time_limit < 1200 ) {
				set_time_limit( 1200 );
			}
		}

		$memory_available = (int) ini_get( 'memory_limit' );

		// lives and dies with the instantiation of the object
		if ( $memory_available < $this->memoryNeeded ) {
			ini_set( 'memory_limit', $this->memoryNeeded . 'M' );
		}

		$this->options = get_option( 'pressbooks_theme_options_mpdf' );
		$this->globalOptions = get_option( 'pressbooks_theme_options_global' );
		$this->bookTitle = get_bloginfo( 'name' );
		$this->exportStylePath = $this->getExportStylePath( 'mpdf' );
		$this->bookMeta = Book::getBookInformation();
		$this->numbered = ( 1 === absint( $this->globalOptions['chapter_numbers'] ) ) ? true : false;

		// Set the access protected "format/xhtml" URL with a valid timestamp and NONCE
		// verbatim from prince/class-pdf.php in pressbooks v4.5.0
		$timestamp = time();
		$md5 = $this->nonce( $timestamp );
		$this->url = home_url() . "/format/xhtml?timestamp={$timestamp}&hashkey={$md5}";
		if ( ! empty( $_REQUEST['preview'] ) ) {
			$this->url .= '&' . http_build_query( [ 'preview' => $_REQUEST['preview'] ] );
		}
	}

	/**
	 * Book Assembly. Create $this->outputPath
	 *
	 * @return bool
	 */
	function convert() {

		$filename = $this->timestampedFileName( '._oss.pdf' );
		$this->outputPath = $filename;
        $contents = file_get_contents( $this->url );
        $doc = new HTML5();
        $dom = $doc->loadHTML( $contents );

		// set up mPDF
		$this->mpdf = new Mpdf(['enableImports' => false, 'tempDir' => _MPDF_TEMP_PATH]);
		$this->mpdf->SetAnchor2Bookmark( 1 );
		$this->mpdf->ignore_invalid_utf8 = true;
		if ( 1 === absint( $this->options['mpdf_mirror_margins'] ) ) {
			$this->mpdf->mirrorMargins = true;
		}
		$this->mpdf->setBasePath( home_url( '/' ) );

		$this->setCss();

		// iterate over the xhtml document
		$this->iterator( $dom );

		$this->mpdf->Output( $this->outputPath, 'F' );

		// TODO trap errors
		return true;
	}

	/**
	 * Add the mpdf Table of Contents.
	 * Note, the functionality of the TOC is limited: its behavior varies
	 * according mirrored margin settings, and will always generate blank pages
	 * after.
	 * http://mpdf1.com/forum/discussion/comment/6417#Comment_6417
	 *
	 */
	function addToc() {

		$options = [
			'paging' => true,
			'links' => true,
			'toc-bookmarkText' => 'toc',
			'toc-preHTML' => '<h1 class="toc">Contents</h1>',
			'toc-margin-left' => 15,
			'toc-margin-right' => 15,
		];
		$this->mpdf->TOCpagebreakByArray( $options );
	}


	/**
	 * Add the cover for the book.
	 */
	function addCover() {
		$page_options = [
			'suppress' => 'on',
			'margin-left' => 15,
			'margin-right' => 15,
		];
		$content = '<div id="half-title-page">';
		$content .= '<h1 class="title">' . $this->bookTitle . '</h1>';
		$content .= '</div>' . "\n";

		if ( ! empty( $this->bookMeta['pb_cover_image'] ) ) {
			$content .= '<div style="text-align:center;"><img src="' . $this->bookMeta['pb_cover_image'] . '" alt="book-cover" title="' . bloginfo( 'name' ) . ' book cover" /></div>';
		}

		$page = [
			'post_type' => 'cover',
			'post_content' => $content,
			'post_title' => '',
			'mpdf_level' => 1,
			'mpdf_omit_toc' => true,
		];

		$this->addPage( $page, $page_options, false, false );
	}

	/**
	 * Add book information page, otherwise known as title page
	 *
	 */
	function addBookInfo() {
		$page_options = [
			'suppress' => 'on',
			'margin-left' => 15,
			'margin-right' => 15,
		];


		$page = [
			'post_title' => '',
			'post_content' => '',
			'post_type' => 'bookinfo',
			'mpdf_level' => 1,
			'mpdf_omit_toc' => true,
		];

		$this->addPage( $page, $page_options, false, false );
	}

	/**
	 * Copyright information on a separate page
	 *
	 */
	function addCopyright() {
		$options = $this->globalOptions;
		$page_options = [
			'suppress' => 'on',
			'margin-left' => 15,
			'margin-right' => 15,
		];


		$page = [
			'post_title' => '',
			'post_content' => '',
			'post_type' => 'bookinfo',
			'mpdf_level' => 1,
			'mpdf_omit_toc' => true,
		];

		$this->addPage( $page, $page_options, false, false );
	}

	/**
	 * Add front matter of a specific/special type
	 *
	 * @param string $type - special content placed ahead of everything else
	 * @param array $contents - book contents
	 */
	function addFrontMatterByType( $type, $contents ) {
		$page_options = [
			'suppress' => 'on',
		];

		foreach ( $contents as $index => $page ) {
			// If we hit non front-matter post types we won't see anymore front-matter
			if ( 'front-matter' !== $page['post_type'] ) {
				return;
			}

			if ( Taxonomy::getFrontMatterType( $page['ID'] ) === $type ) {
				$page['mpdf_omit_toc'] = true;
				$this->addPage( $page, $page_options, false, false );
			}
		}
	}

	/**
	 * Adds front matter, resets the page numbering on the first loop,
	 * romanizes the numeric style
	 *
	 * @param array $contents
	 */
	function addFrontMatter( array $contents ) {

		$first_iteration = true;
		$page_options = [
			'pagenumstyle' => 'i',
			'margin-left' => 15,
			'margin-right' => 15,
		];

		foreach ( $contents as $front_matter ) {
			// safety
			$type = Taxonomy::getFrontMatterType( $front_matter['ID'] );
			if ( 'dedication' === $type || 'epigraph' === $type || 'title-page' === $type || 'before-title' === $type ) {
					continue; // Skip
			}

			// only reset the page number on first iteration
			$page_options['resetpagenum'] = ( true === (bool) $first_iteration ) ? 1 : 0;

			// assumes the array of book contents is in order
			if ( 'front-matter' !== $front_matter['post_type'] ) {
				return;
			}
			if ( ! empty( $front_matter['post_content'] ) ) {
				$this->addPage( $front_matter, $page_options, true, true );
				$first_iteration = false;
			}
		}
	}

	function addPartsAndChapters( $contents ) {
		// change the numbering system to numeric
		// iterate through, parts, chapters, back-matter
		$first_iteration = true;
		$i = 1;
		$page_options = [];
		foreach ( $contents as $page ) {

			if ( 'front-matter' === $page['post_type'] ) { continue; //skip all front-matter
			}

			if ( true === (bool) $first_iteration ) {
				$page_options['pagenumstyle'] = 1;
			}
			$page['chapter_num'] = $i;
			$this->addPage( $page, $page_options );
			$first_iteration = false;
			if ( 'part' !== $page['post_type'] ) {
				$i++;
			}
		}
	}

	/**
	 * Add a page to the pdf
	 *
	 * @param array $page - the content
	 * @param array $page_options - numbering reset, style, suppress adding to TOC
	 * @param boolean $display_footer turn on/off footer display
	 * @param boolean $display_header turn on/off header display
	 * @return boolean
	 */
	function addPage( $page, $page_options = [], $display_footer = true, $display_header = true ) {
		// defaults
		$defaults = [
			'suppress' => 'off',
			'resetpagenum' => 0,
			'pagenumstyle' => 1,
			'margin-right' => $this->options['mpdf_right_margin'],
			'margin-left' => $this->options['mpdf_left_margin'],
			'sheet-size' => $this->options['mpdf_page_size'],
		];

		$options = \wp_parse_args( $page_options, $defaults );
		$toc_entry = ( 'chapter' === $page['post_type']  && true === $this->numbered ) ? $page['chapter_num'] . ' ' . $page['post_title'] : $page['post_title'];

		if ( ! empty( $page['post_content'] ) || 'part' === $page['post_type'] ) {

			$this->mpdf->SetFooter( $this->getFooter( $display_footer, $this->bookTitle . '| | {PAGENO}' ) );
			$this->mpdf->SetHeader( $this->getHeader( $display_header, '' ) );

			$this->mpdf->AddPageByArray( $options );

			if ( empty( $page['mpdf_omit_toc'] ) ) {
				$this->mpdf->TOC_Entry( $this->getTocEntry( $toc_entry ), $page['mpdf_level'] );
				$this->mpdf->Bookmark( $this->getBookmarkEntry( $page ), $page['mpdf_level'] );
			}

			// TODO Make this hookable.
			$this->mpdf->WriteHTML( '' );
			return true;
		}

		return false;
	}

	protected function iterator( \DOMDocument $dom ) {
		// assumes xhtml output puts all pages into first level children of body node
		$parts = $dom->getElementsByTagName( 'body' )->item( 0 )->childNodes;

		foreach ( $parts as $part ) {
			// avoid text nodes
			if ( XML_ELEMENT_NODE === $part->nodeType ) {
				$page_options = [];
				$defaults = [
					'suppress' => 'off',
					'resetpagenum' => 0,
					'pagenumstyle' => 1,
					'margin-right' => $this->options['mpdf_right_margin'],
					'margin-left' => $this->options['mpdf_left_margin'],
					'sheet-size' => $this->options['mpdf_page_size'],
				];

				$options = \wp_parse_args( $page_options, $defaults );
				$this->mpdf->AddPageByArray( $options );
				$html = $dom->saveHTML( $part );
				$this->mpdf->WriteHTML( $html );
			}
		}
	}

	/**
	 * Return the Table of Contents entry for this page.
	 *
	 * @param string $page
	 * @return string
	 */
	function getTocEntry( $page ) {

		// allow override
		$entry = apply_filters( 'mpdf_get_toc_entry', $page );
		// sanitize
		$entry = Sanitize\filter_title( $entry );

		return $entry;
	}

	/**
	 * Return the PDF bookmark entry for this page
	 * should be unique, using static variable for cheap cache
	 *
	 * @staticvar int $id - to avoid collisions with identical page titles
	 * @param array $page
	 * @return string
	 */
	function getBookmarkEntry( $page ) {
		static $id = 1;
		$entry = $id . ' ' . $page['post_title'];
		$id++;

		return $entry;
	}





	/**
	 * Return formatted footers.
	 *
	 * @param bool $display
	 * @param string $content
	 *   The post type being added to the page.
	 *
	 * @return string
	 */
	function getFooter( $display = true, $content = '' ) {
		// bail early
		if ( false === (bool) $display ) {
			return '';
		}

		// override
		$footer = apply_filters( 'mpdf_get_footer', $content );
		// sanitize
		$footer = Sanitize\filter_title( $footer );

		return $footer;
	}

	/**
	 * Return formatted headers.
	 *
	 * @param bool $display
	 * @param string $content
	 *  The post type being added to the page
	 *
	 * @return string
	 */
	function getHeader( $display = true, $content = '' ) {
		// bail early
		if ( false === (bool) $display ) {
			return '';
		}

		// override
		$header = apply_filters( 'mpdf_get_header', $content );
		//sanitize
		$header = Sanitize\filter_title( $header );

		return $header;
	}


	/**
	 * Get current child and parent theme css files. Child themes only have one parent
	 * theme, and 99% of the time this is 'Luther' or /pressbooks-book/ whose stylesheet is
	 * named 'style.css'
	 *
	 * @param object $theme
	 * @return string $css
	 */
	function getThemeCss( $theme ) {

		$css = '';

		// get parent theme files
		if ( is_object( $theme->parent() ) ) {
			$parent_files = $theme->parent()->get_files( 'css' );

			// exclude admin files
			$parents = $this->stripUnwantedStyles( $parent_files );

			// hopefully there is something left for us to grab
			if ( ! empty( $parents ) ) {
				foreach ( $parents as $parent ) {
					$css .= file_get_contents( $parent ) . "\n";
				}
			}
		}
		// get child theme files
		$child_files = $theme->get_files( 'css' );
		// exclude admin files
		$children = $this->stripUnwantedStyles( $child_files );

		if ( ! empty( $children ) ) {
			foreach ( $children as $child ) {
				$css .= file_get_contents( $child ) . "\n";
			}
		}

		return $css;
	}

	/**
	 * Helper function to omit unwanted stylesheets in the output
	 *
	 * @param array $styles
	 * @return array $sytles
	 */
	private function stripUnwantedStyles( array $styles ) {

		$unwanted = [
			'editor-style.css',
		];

		foreach ( $unwanted as $key ) {
			if ( array_key_exists( $key, $styles ) ) {
				unset( $styles[ $key ] );
			}
		}
		return $styles;
	}

	/**
	 * Add all css files
	 */
	function setCss() {
		$css = '';

		// check for child theme export file
		$cssfile = $this->getExportStylePath( 'mpdf' );

		// if empty, try the parent theme export directory
		if ( empty( $cssfile ) ) {
			$cssfile = realpath( get_template_directory() . '/export/mpdf/style.css' );
		}

		if ( is_string( $cssfile ) && ! empty( $cssfile ) ) {
			$css .= file_get_contents( $cssfile ) . "\n";
		}

		// grab the web theme, ONLY as a backup
		if ( empty( $css ) ) {
			$theme = wp_get_theme();
			$css = $this->getThemeCss( $theme );
		}

		// Theme options override
		$css .= apply_filters( 'pb_mpdf_css_override', $css ) . "\n";

		if ( ! empty( $css ) ) {
			$this->mpdf->WriteHTML( $css, self::MODE_CSS );
		}
	}

	/**
	 * Check the sanity of $this->outputPath
	 *
	 * @return bool
	 */
	function validate() {

		if ( ! $this->isPdf( $this->outputPath ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Verify if file has 'application/pdf' mimeType.
	 *
	 * @param string $file
	 *
	 * @return bool
	 */
	protected function isPdf( $file ) {

		$mime = static::mimeType( $file );

		return ( strpos( $mime, 'application/pdf' ) !== false );
	}

	/**
	 * Does array of chapters have at least one export? Recursive.
	 *
	 * @param array $chapters
	 * @return bool
	 */
	protected function atLeastOneExport( array $chapters ) {

		foreach ( $chapters as $key => $val ) {
			if ( is_array( $val ) ) {
				if ( $this->atLeastOneExport( $val ) ) {
					return true;
				}
			} elseif ( 'export' === (string) $key && $val ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Dependency check.
	 *
	 * @return bool
	 */
	static function hasDependencies() {
		return true;
	}

	/**
	 * Add to export formats.
	 *
	 * @since 2.0.0
	 * @author Book Oven Inc. <code@pressbooks.com>
	 *
	 * @param array $formats a multidimensional array of standard and exotic formats
	 * @return array $formats
	 */
	static function addToFormats( $formats ) {
		$formats['standard'] = [ 'mpdf' => __( 'PDF (mPDF)', 'pressbooks-mpdf' ) ] + $formats['standard'];
		return $formats;
	}

	/**
	 * Add to export modules.
	 *
	 * @since 2.0.0
	 * @author Book Oven Inc. <code@pressbooks.com>
	 *
	 * @param array $modules an array of active export module classnames
	 * @return array $modules
	 */
	static function addToModules( $modules ) {
		if ( isset( $_POST['export_formats']['mpdf'] ) ) { // @codingStandardsIgnoreLine
			$modules[] = '\BCcampus\Modules\Export\Mpdf\Pdf';
		}
		return $modules;
	}
}
