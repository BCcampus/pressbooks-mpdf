<?php
/**
 * @author    Brad Payne <brad@bradpayne.ca>
 * @license   GPLv2
 * @copyright Brad Payne
 *
 */

add_filter( 'pb_export_formats', [ '\Pressbooks\Modules\Export\Mpdf\Pdf', 'addToFormats' ] );
add_filter( 'pb_active_export_modules', [ '\Pressbooks\Modules\Export\Mpdf\Pdf', 'addToModules' ] );
add_filter( 'pb_theme_options_tabs', [ '\Pressbooks\Modules\ThemeOptions\MpdfOptions', 'addTab' ] );
add_filter( 'pb_mpdf_css_override', [ '\Pressbooks\Modules\ThemeOptions\MpdfOptions', 'scssOverrides' ] );
