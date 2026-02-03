<?php
/**
 * Mobile Breadcrumb Navigation
 *
 * Validates breadcrumb implementation for mobile usability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Navigation
 * @since      1.2602.1450
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
class-diagnostic-exit;
}

/**
 * Mobile Breadcrumb Navigation
 *
 * Ensures breadcrumb navigation exists and is mobile-friendly
 * with appropriate sizing and schema markup.
 *
 * @since 1.2602.1450
 */
class Diagnostic_Mobile_Breadcrumb extends Diagnostic_Base {

class-diagnostic-protected static $slug = 'mobile-breadcrumb-navigation';
class-diagnostic-protected static $title = 'Mobile Breadcrumb Navigation';
class-diagnostic-protected static $description = 'Validates breadcrumbs for mobile usability';
class-diagnostic-protected static $family = 'navigation';

class-diagnostic-public static function check() {
class-diagnostic-$html = self::get_page_html();
class-diagnostic-if ( ! $html ) {
class-diagnostic- null;
class-diagnostic-}

class-diagnostic-$issues = array();

class-diagnostic-// Check for breadcrumb existence
class-diagnostic-$has_breadcrumb = preg_match( '/class\s*=\s*["\'][^"\']*breadcrumb[^"\']*["\']|typeof\s*=\s*["\']BreadcrumbList["\']/i', $html );

class-diagnostic-if ( ! $has_breadcrumb ) {
class-diagnostic-o-breadcrumbs',
class-diagnostic-o breadcrumb navigation detected',
class-diagnostic-o-breadcrumb-schema',
class-diagnostic-g schema.org markup',
class-diagnostic-dly sizing
class-diagnostic-preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
class-diagnostic-$css = implode( "\n", $style_matches[1] ?? array() );

class-diagnostic-if ( preg_match( '/\.breadcrumb[^{]*{[^}]*font-size\s*:\s*([0-9]+)px/', $css, $size_match ) ) {
class-diagnostic-t_size = (int) $size_match[1];
class-diagnostic-t_size < 14 ) {
class-diagnostic-t_size,
class-diagnostic- null;
class-diagnostic-}

class-diagnostic-return array(
class-diagnostic-' => sprintf( __( 'Found %d breadcrumb issues', 'wpshadow' ), count( $issues ) ),
class-diagnostic-not easily navigate site hierarchy on mobile', 'wpshadow' ),
class-diagnostic-k' => 'https://wpshadow.com/kb/breadcrumbs',
class-diagnostic-);
class-diagnostic-}

class-diagnostic-private static function get_page_html(): ?string {
class-diagnostic-$response = wp_remote_get( home_url( '/' ), array( 'timeout' => 5, 'sslverify' => false ) );
class-diagnostic-if ( is_wp_error( $response ) ) {
class-diagnostic- null;
class-diagnostic-}
class-diagnostic-return wp_remote_retrieve_body( $response );
class-diagnostic-}
}
