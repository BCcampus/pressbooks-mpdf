=== Pressbooks mPDF ===
Contributors: bdolor, greatislander
Donation link: https://github.com/BCcampus/pressbooks-mpdf
Tags: pressbooks, textbook, mPDF
Requires at least: 4.8.0
Tested up to: 4.8.0
Stable tag: 2.0.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pressbooks mPDF

== Description ==

[mPDF](http://www.mpdf1.com/mpdf/index.php) is an open source PHP class that generates PDF files from HTML.
mPDF is also released under a GPLv2 license and made by people other than the authors of this plugin.

The mPDF class is large and not a development focus for pressbooks.com. Where the mPDF class has previously been a part of the [pressbooks](https://wordpress.org/plugins/pressbooks), it has now been removed.
Installing this plugin will restore the previous functionality that open source users have come to expect if the license fee for [PrinceXML](http://www.princexml.com/) is a barrier.

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
2. Search for 'Pressbooks mPDF'
3. Click 'Network Activate'

= OR, upload manually =

1. Upload `pressbooks-mpdf` to the `/wp-content/plugins/` directory
2. Activate the plugin at the network level, through the 'Plugins' menu in WordPress

== Changelog ==

See: https://github.com/BCcampus/pressbooks-mpdf/commits/master for more detail

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
