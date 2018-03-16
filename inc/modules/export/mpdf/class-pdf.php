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
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Masterminds\HTML5;
use Pressbooks\Book;
use Pressbooks\Sanitize;
use Pressbooks\Modules\Export\Prince;

class Pdf extends Prince\Pdf {

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

		$this->options         = get_option( 'pressbooks_theme_options_mpdf' );
		$this->globalOptions   = get_option( 'pressbooks_theme_options_global' );
		$this->bookTitle       = get_bloginfo( 'name' );
		$this->exportStylePath = $this->getExportStylePath( 'prince' );
		$this->bookMeta        = Book::getBookInformation();

		// Set the access protected "format/xhtml" URL with a valid timestamp and NONCE
		// verbatim from prince/class-pdf.php in pressbooks v4.5.0
		$timestamp = time();
		$md5       = $this->nonce( $timestamp );
		$this->url = home_url() . "/format/xhtml?timestamp={$timestamp}&hashkey={$md5}";
		if ( ! empty( $_REQUEST['preview'] ) ) {
			$this->url .= '&' . http_build_query(
				[
					'preview' => $_REQUEST['preview'],
				]
			);
		}
	}

	/**
	 * Book Assembly. Create $this->outputPath
	 *
	 * @return bool
	 */
	function convert() {

		$filename                = $this->timestampedFileName( '._oss.pdf' );
		$this->outputPath        = $filename;
		$contents                = file_get_contents( $this->url );
		$doc                     = ( class_exists( HTML5::class ) ) ? new HTML5() : new \DOMDocument();
		$doc->preserveWhiteSpace = false;
		$dom                     = $doc->loadHTML( $contents );

		$config = $this->setConfigVariables();

		try {
			$this->mpdf = new Mpdf( $config );
			$this->setDocumentMeta();

			// @see https://mpdf.github.io/reference/mpdf-functions/overview.html
			$this->mpdf->SetBasePath( home_url( '/' ) );
			$this->mpdf->SetCompression( true );

			// iterate over the xhtml domdocument
			$this->iterator( $dom );

			/****************************************
			 * alternate route NOTES:
			 * dumping the whole xhtml document in and using prince css
			 * works but MPDF TOC and Bookmarks don't build
			 * works but no page numbering
			 * works but is more memory intensive for mpdf (error msg below)
			 * * The HTML code size is larger than pcre.backtrack_limit 1000000.
			 * * You should use WriteHTML() with smaller string lengths
			 * works but mpdf headers and footer functionality is lost
			 *****************************************/
			//$this->mpdf->WriteHTML( $contents );

			// make the thing
			$this->mpdf->Output( $this->outputPath, 'F' );

		} catch ( MpdfException $e ) {
			error_log( $e->getMessage() );
		}

		return true;
	}

	/**
	 * Give Mpdf all the things
	 * @see https://github.com/mpdf/mpdf/blob/development/src/Config/ConfigVariables.php
	 *
	 */
	private function setConfigVariables() {
		// CSS File
		$css      = $this->kneadCss();
		$css      = $this->filterCss( $css );
		$css_file = $this->createTmpFile();
		file_put_contents( $css_file, $css );

		$map_pb_to_mpdf = [
			'mpdf_page_size'      => 'format',
			'mpdf_margin_left'    => 'margin_left',
			'mpdf_margin_right'   => 'margin_right',
			'mpdf_mirror_margins' => 'mirrorMargins',

		];

		// cherry picked
		$config = [
			'mode'                   => 's',
			'format'                 => 'Letter',
			'default_font_size'      => 0,
			'default_font'           => '',
			'margin_left'            => 15,
			'margin_right'           => 15,
			'margin_top'             => 16,
			'margin_bottom'          => 16,
			'margin_header'          => 9,
			'margin_footer'          => 9,
			'orientation'            => 'P',
			'enableImports'          => false,
			'anchor2Bookmark'        => 1,
			'mirrorMargins'          => 1,
			'tempDir'                => _MPDF_TEMP_PATH,
			'defaultCssFile'         => $css_file,
			'autoLangToFont'         => true,
			'ignore_invalid_utf8'    => true,
			'defaultfooterline'      => 0,
			'defaultheaderline'      => 0,
			'defaultheaderfontstyle' => 'I',
			'defaultfooterfontstyle' => 'I',
			'shrink_tables_to_fit'   => 1,
			'use_kwt'                => true,
			//          'debug'                => true,
		];

		// user config overrides defaults
		foreach ( $map_pb_to_mpdf as $k => $v ) {
			if ( isset( $this->options[ $k ] ) ) {
				$config[ $v ] = $this->options[ $k ];
			}
		}

		return $config;

	}

	/**
	 * Sets Available PDF Document Metadata
	 * @see https://mpdf.github.io/setting-pdf-file-properties/document-metadata.html
	 *
	 */
	protected function setDocumentMeta() {
		( isset( $this->bookMeta['pb_title'] ) ) ? $this->mpdf->SetTitle( $this->bookMeta['pb_title'] ) : '';
		( isset( $this->bookMeta['pb_authors'] ) ) ? $this->mpdf->SetAuthor( $this->bookMeta['pb_authors'] ) : '';
		( isset( $this->bookMeta['pb_publisher'] ) ) ? $this->mpdf->SetCreator( $this->bookMeta['pb_publisher'] ) : '';
		( isset( $this->bookMeta['pb_primary_subject'] ) ) ? $this->mpdf->SetSubject( $this->bookMeta['pb_primary_subject'] ) : '';
		( isset( $this->bookMeta['pb_keywords_tags'] ) ) ? $this->mpdf->SetKeywords( $this->bookMeta['pb_keywords_tags'] ) : '';

	}

	/**
	 * Add the mpdf Table of Contents.
	 * Note, the functionality of the TOC is limited: its behavior varies
	 * according mirrored margin settings, and will always start on an odd page and
	 * is likely to generate unwanted blank pages after.
	 * http://mpdf1.com/forum/discussion/comment/6417#Comment_6417
	 *
	 */
	function addToc() {

		$options = [
			'paging'           => true,
			'links'            => true,
			'tocindent'        => 1,
			'suppress'         => 'on',
			'toc_bookmarkText' => 'Contents',
			'toc_preHTML'      => '<h1 class="toc">Contents</h1>',
		];

		$this->mpdf->TOCpagebreakByArray( $options );
	}


	/**
	 * Add the cover for the book.
	 */
	function addCover() {
		$page_options = [
			'suppress'     => 'on',
			'margin-left'  => 15,
			'margin-right' => 15,
		];
		$content      = '<div id="half-title-page">';
		$content      .= '<h1 class="title">' . $this->bookTitle . '</h1>';
		$content      .= '</div>' . "\n";

		if ( ! empty( $this->bookMeta['pb_cover_image'] ) ) {
			$content .= '<div style="text-align:center;"><img src="' . $this->bookMeta['pb_cover_image'] . '" alt="book-cover" title="' . bloginfo( 'name' ) . ' book cover" /></div>';
		}

		$this->mpdf->AddPageByArray( $page_options );
		$this->mpdf->WriteHTML( $content );
	}


	/**
	 * @param \DOMDocument $dom
	 */
	protected function iterator( \DOMDocument $dom ) {
		// assumes xhtml output has all pages in the first level children of body node
		// ex: body->div
		$pages = $dom->getElementsByTagName( 'body' )->item( 0 )->childNodes;

		// first thing's first, gotta have a purty pikcher
		if ( 1 === $this->options['mpdf_include_cover'] ) {
			$this->addCover();
		}

		foreach ( $pages as $page ) {
			// avoid text nodes
			if ( XML_ELEMENT_NODE === $page->nodeType ) {

				/****************************************
				 * Logic
				 *****************************************/
				$context_class = substr( $page->getAttribute( 'class' ), 0, 4 );
				$context_id    = substr( $page->getAttribute( 'id' ), 0, 3 );

				// Mpdf has its own table of contents mechanism
				if ( 0 === strcmp( $context_id, 'toc' ) ) {
					if ( 1 === $this->options['mpdf_include_toc'] ) {
						$this->addToc();
					}
					// prevent further processing
					continue;
				}

				switch ( $context_class ) {

					case 'fron':
						$display_header = false;
						$display_footer = true;
						$page_options   = [
							'suppress' => 'off',
							'pagenumstyle' => 'i',
						];
						$toc_level      = 0;
						$element        = 'h1';
						$class          = 'front-matter-title';
						$title          = $this->getNodeValue( $page, $element, $class );
						$add_to_toc     = true;
						break;

					case 'chap':
						$display_header = true;
						$display_footer = true;
						$page_options   = [
							'suppress' => 'off',
							'pagenumstyle' => '1',
						];
						$toc_level      = 1;
						$element        = 'h2';
						$class          = 'chapter-title';
						$title          = $this->getNodeValue( $page, $element, $class );
						$add_to_toc     = true;
						break;

					case 'part':
						$display_header = false;
						$display_footer = true;
						$page_options   = [
							'suppress' => 'on',
							'pagenumstyle' => '1',
						];
						$toc_level      = 0;
						$element        = 'h1';
						$class          = 'part-title';
						$title          = $this->getNodeValue( $page, $element, $class );
						$add_to_toc     = true;
						break;

					case 'back':
						$display_header = false;
						$display_footer = true;
						$page_options   = [
							'suppress' => 'off',
							'pagenumstyle' => '1',
						];
						$toc_level      = 0;
						$element        = 'h1';
						$class          = 'back-matter-title';
						$title          = $this->getNodeValue( $page, $element, $class );
						$add_to_toc     = true;
						break;

					default:
						$display_header = false;
						$display_footer = false;
						$page_options   = [
							'suppress'     => 'on',
							'margin_left'  => 15,
							'margin_right' => 15,
						];
						$toc_level      = 0;
						$element        = '';
						$class          = '';
						$title          = '';
						$add_to_toc     = false;

				}

				/****************************************
				 * Add Page to Document Array
				 *****************************************/
				$this->mpdf->AddPageByArray( $page_options );

				/****************************************
				 * Table of Contents
				 *****************************************/
				if ( $add_to_toc && 1 === $this->options['mpdf_include_toc'] ) {
					$this->mpdf->TOC_Entry( $this->getTocEntry( $title ), $toc_level );
					$this->mpdf->Bookmark( $this->getBookmarkEntry( $title, $class ), $toc_level );
				}

				/****************************************
				 * Headers and Footers
				 *****************************************/
				$footer = ( $display_footer ) ? $this->getFooter( $display_footer ) : '';
				$header = ( $display_header ) ? $this->getHeader( $display_header ) : '';

				$this->mpdf->SetFooter( $footer );
				$this->mpdf->SetHeader( $header );

				/****************************************
				 * Do the thing
				 *****************************************/
				$html = $dom->saveHTML( $page );
				$this->mpdf->WriteHTML( $html );
			}
		}

	}

	/**
	 * Return the Table of Contents entry for this page.
	 *
	 * @param $title
	 *
	 * @return string
	 */
	function getTocEntry( $title ) {

		// allow override
		$entry = apply_filters( 'mpdf_get_toc_entry', $title );

		// sanitize
		$entry = Sanitize\filter_title( $entry );

		return $entry;
	}

	/**
	 * Return the PDF bookmark entry for this page
	 * should be unique, using static variable for cheap cache
	 *
	 * @staticvar int $id - to avoid collisions with identical page titles
	 *
	 * @param $title
	 *
	 * @return string
	 */
	function getBookmarkEntry( $title, $class ) {
		static $part_id = 1;
		static $chap_id = 1;

		if ( 'part-title' == $class ) {
			$entry = $part_id . '. ' . $title;
			$part_id ++;
		} elseif ( 'chapter-title' == $class ) {
			$entry = $chap_id . '. ' . $title;
			$chap_id ++;
		} else {
			$entry = $title;
		}

		return $entry;
	}


	/**
	 * Return formatted footers.
	 *
	 * @param bool $display
	 * @param string $content
	 *
	 * @return string
	 */
	function getFooter( bool $display, $content = '' ) {

		// bail early
		if ( false === $display ) {
			return '';
		}

		// default content if none provided
		$content = ( empty( $content ) ) ? ' | {PAGENO} | ' : $content;

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
	 *
	 * @return string
	 */
	function getHeader( bool $display, $content = '' ) {

		// bail early
		if ( false === $display ) {
			return '';
		}

		$content = ( empty( $content ) ) ? ' | | ' . $this->bookTitle . '' : $content;

		// override
		$header = apply_filters( 'mpdf_get_header', $content );

		//sanitize
		$header = Sanitize\filter_title( $header );

		return $header;
	}

	/**
	 * get the value of a node
	 *
	 * @param \DOMElement $content
	 * @param $element
	 * @param $class
	 *
	 * @return string
	 */
	private function getNodeValue( \DOMElement $content, $element, $class ) {
		$title = '';

		if ( ! empty( $content ) ) {
			$headings = $content->getElementsByTagName( $element );

			foreach ( $headings as $heading ) {
				$title = ( $class === $heading->getAttribute( 'class' ) ) ? $heading->nodeValue : '';
				if ( $title ) {
					break;
				}
			}
		}

		return $title;
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
	 *
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

	private function filterCss( $css ) {
		$filtered = '';

		// primary purpose is to deal with how mpdf class handles @page
		if ( ! empty( $css ) ) {
			// page breaks in mpdf are created with 'AddPage()'
			$filtered = preg_replace( '/page-break-(before|after|inside)\:(\s?)(right|auto|left|always|inherit);/i', 'page-break-$1: avoid;', $css );

			// mpdf has its own class for toc
			// 2018/02/23 - removing this, prince pdf options interfere ex. 'display:none'
			//$filtered = preg_replace( '/#toc\s?/iU', '.mpdf_toc ', $filtered );

			// reference to page selectors force unwanted page breaks
			$filtered = preg_replace( '/page\:\s?(.*);/iU', '', $filtered );

			// page breaks created with every change in @page selector
			$filtered = preg_replace( '/@page\s?(.*)\s{/iU', 'body {', $filtered );

			// Mpdf has limited @page support
			$filtered = preg_replace( '/(?:@(top*|bottom*|right*|left*|footnotes)\s?(.*)\s?{(.*)})/isU', '', $filtered );

		}

		$mpdf_css = 'div.mpdf_toc_level_0{line-height:1.5;margin-left:0;padding-right:0}div.mpdf_toc_level_1{margin-left:2em;text-indent:-2em;padding-right:0}div.mpdf_toc_level_2{margin-left:4em;text-indent:-2em;padding-right:0}';

		return $mpdf_css . $filtered;

	}


}
