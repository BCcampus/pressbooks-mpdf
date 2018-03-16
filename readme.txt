=== mPDF for Pressbooks ===
Contributors: bdolor, greatislander
Donation link: https://github.com/BCcampus/pressbooks-mpdf
Tags: pressbooks, textbook, mPDF
Requires at least: 4.9.4
Tested up to: 4.9.4
Stable tag: 3.1.1
Requires PHP: 7.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

mPDF for Pressbooks

== Description ==

[mPDF](https://github.com/mpdf/mpdf) is an open source PHP class that generates PDF files from HTML.

This plugin is an open source alternative for PDF generation if the license fee for [PrinceXML](http://www.princexml.com/) is a barrier for users of Pressbooks.

[Pressbooks](https://wordpress.org/plugins/pressbooks/) is a requirement in order for this plugin to do anything useful.

== Installation ==

IMPORTANT!

You must first install [Pressbooks](https://github.com/pressbooks/pressbooks). This plugin won't work without it.
The Pressbooks github repository is updated frequently. [Stay up to date](https://github.com/pressbooks/pressbooks/tree/master).

= Using Git =

1. cd /wp-content/plugins
2. git clone https://github.com/BCcampus/pressbooks-mpdf.git
3. Activate the plugin at the network level, through the 'Plugins' menu in WordPress

= OR, go to the WordPress Dashboard =

1. Navigate to the Network Admin -> Plugins
2. Search for 'mPDF for Pressbooks'
3. Click 'Network Activate'

= OR, upload manually =

1. Upload `pressbooks-mpdf` to the `/wp-content/plugins/` directory
2. Activate the plugin at the network level, through the 'Plugins' menu in WordPress

== FAQ ==

= What is mPDF?

[mPDF](https://mpdf.github.io/: A PHP library to generate PDF files from HTML with Unicode/UTF-8 and CJK support

It is based on FPDF and HTML2FPDF with a number of enhancements.


== Screenshots ==

1. The mPDF options of the PDF export.
2. Select the PDF (mPDF) format for exporting your book as PDF.

== Changelog ==

See: https://github.com/BCcampus/pressbooks-mpdf/commits/master for more detail

= 3.1.1 (2018/03/13) =
* fix for Table of Contents missing titles (props @beckej13820 for reporting)
* update toc bookmark numbering (props @colomet for the suggestion)

= 3.1.0 (2018/02/27) =
* compatibility with PB5
* updating to mPDF v7.0.3
* plugin name change

= 3.1.0-rc.1 (2018/02/23) =
* Release Candidate, compatibility with PB 5

= 3.0.0 (2017/12/15) =
* Compatibility with Pressbooks 4.5.0
* Requires PHP 7+ and WP 4.9.1
* Updated mpdf to v7.0.2
* Theme parity (export style matches theme)
* Uses XHTML file as data source (same as PrinceXML)

= 2.0.0 =
* Compatibility with Pressbooks 4.0.0
* Moved temp directories into uploads directory, eliminating the need for making subdirectories of the plugin writeable (fixes #19)
* Fixed an issue with mPDF theme options introduced by an earlier release of Pressbooks

= 1.7.0 (2017/05/31) =
* compatibility with Pressbooks 3.9.9

= 1.6.2.3 (2017/04/20) =
* compatibility with Pressbooks 3.9.8.2

= 1.6.2.2 (2017/04/18) =
* fix for redeclare htmlawed error

= 1.6.2.1 (2017/04/10) =
* compatibility with modifications to htmlawed dependency

= 1.6.2 (2017/04/07) =
* compatibility with Pressbooks 3.9.8 (props @greatislander)

= 1.6.1 (2017/02/08) =
* updated mPDF dependency to 6.1
* switched to composer for dependency management

= 1.0.1 (2015/12/15) =
* initial release

== How to contribute code ==

Pull requests are enthusiastically received **and** scrutinized for quality.

* The best way is to initiate a pull request on [GitHub](https://github.com/BCcampus/pressbooks-mpdf).
