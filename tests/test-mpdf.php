<?php

/**
 * Class TestMpdf
 *
 * @package Pressbooks_Mpdf
 */

class TestMpdf extends WP_UnitTestCase {
	/**
	 * @var \ExportMock
	 */
	protected $export;

	/**
	 *
	 */
	public function setUp() {
		parent::setUp();
		//$this->export = new ExportMock();
	}

	/**
	 *
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

}
