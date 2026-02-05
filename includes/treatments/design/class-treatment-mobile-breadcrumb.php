<?php
/**
 * Mobile Breadcrumb Navigation
 *
 * Validates breadcrumb implementation for mobile usability.
 *
 * @package    WPShadow
 * @subpackage Treatments\Navigation
 * @since      1.602.1450
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
class-treatment-exit;
}

/**
 * Mobile Breadcrumb Navigation
 *
 * Ensures breadcrumb navigation exists and is mobile-friendly
 * with appropriate sizing and schema markup.
 *
 * @since 1.602.1450
 */
class Treatment_Mobile_Breadcrumb extends Treatment_Base {

class-treatment-protected static $slug = 'mobile-breadcrumb-navigation';
class-treatment-protected static $title = 'Mobile Breadcrumb Navigation';
class-treatment-protected static $description = 'Validates breadcrumbs for mobile usability';
class-treatment-protected static $family = 'navigation';

class-treatment-public static function check() {
class-treatment-$html = self::get_page_html();
class-treatment-if ( ! $html ) {
class-treatment- null;
class-treatment-}

class-treatment-$issues = array();

class-treatment-// Check for breadcrumb existence
class-treatment-$has_breadcrumb = preg_match( '/class\s*=\s*["\'][^"\']*breadcrumb[^"\']*["\']|typeof\s*=\s*["\']BreadcrumbList["\']/i', $html );

class-treatment-if ( ! $has_breadcrumb ) {
class-treatment-o-breadcrumbs',
class-treatment-o breadcrumb navigation detected',
class-treatment-o-breadcrumb-schema',
class-treatment-g schema.org markup',
class-treatment-dly sizing
class-treatment-preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
class-treatment-$css = implode( "\n", $style_matches[1] ?? array() );

class-treatment-if ( preg_match( '/\.breadcrumb[^{]*{[^}]*font-size\s*:\s*([0-9]+)px/', $css, $size_match ) ) {
class-treatment-t_size = (int) $size_match[1];
class-treatment-t_size < 14 ) {
class-treatment-t_size,
class-treatment- null;
class-treatment-}

class-treatment-return array(
class-treatment-' => sprintf( __( 'Found %d breadcrumb issues', 'wpshadow' ), count( $issues ) ),
class-treatment-not easily navigate site hierarchy on mobile', 'wpshadow' ),
class-treatment-k' => 'https://wpshadow.com/kb/breadcrumbs',
class-treatment-);
class-treatment-}

class-treatment-private static function get_page_html(): ?string {
class-treatment-return Treatment_HTML_Helper::fetch_homepage_html(
class-treatment-	array(
class-treatment-		'timeout'   => 5,
class-treatment-		'sslverify' => false,
class-treatment-	)
class-treatment-);
class-treatment-}
}
