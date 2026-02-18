<?php
/**
 * Mobile Anchor Link Performance
 *
 * Validates smooth scrolling and anchor link behavior on mobile.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Navigation
 * @since      1.602.1450
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
class-diagnostic-exit;
}

class Diagnostic_Mobile_Anchor_Links extends Diagnostic_Base {

class-diagnostic-protected static $slug = 'mobile-anchor-link-performance';
class-diagnostic-protected static $title = 'Mobile Anchor Link Performance';
class-diagnostic-protected static $description = 'Validates anchor links for mobile';
class-diagnostic-protected static $family = 'navigation';

class-diagnostic-public static function check() {
class-diagnostic-$html = self::get_page_html();
class-diagnostic-if ( ! $html ) {
class-diagnostic- null;
class-diagnostic-}

class-diagnostic-$issues = array();

class-diagnostic-// Check for anchor links
class-diagnostic-$anchor_count = preg_match_all( '/href\s*=\s*["\']#[^"\']+["\']/i', $html, $matches );

class-diagnostic-if ( $anchor_count === 0 ) {
class-diagnostic- null; // No anchor links to check
class-diagnostic-}

class-diagnostic-// Check for smooth scroll CSS
class-diagnostic-preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
class-diagnostic-$css = implode( "\n", $style_matches[1] ?? array() );

class-diagnostic-$has_smooth_scroll = preg_match( '/scroll-behavior\s*:\s*smooth/i', $css );

class-diagnostic-if ( ! $has_smooth_scroll ) {
class-diagnostic-o-smooth-scroll',
class-diagnostic-chor links jump instantly (no smooth scrolling)',
class-diagnostic-\s*:\s*fixed.*?header|header.*?position\s*:\s*fixed/is', $css );

class-diagnostic-if ( $has_fixed_header ) {
class-diagnostic-g-top|scroll-margin-top/i', $css );

class-diagnostic-o-scroll-offset',
class-diagnostic-chor link targets',
class-diagnostic-dling
class-diagnostic-$has_js_scroll = preg_match( '/scrollIntoView|scrollTo\(|\.animate.*?scrollTop/i', $html );

class-diagnostic-if ( ! $has_js_scroll && ! $has_smooth_scroll ) {
class-diagnostic-o-scroll-implementation',
class-diagnostic-o smooth scroll implementation detected',
class-diagnostic- null;
class-diagnostic-}

class-diagnostic-return array(
class-diagnostic-' => sprintf( __( 'Found %d anchor link issues', 'wpshadow' ), count( $issues ) ),
class-diagnostic-chor_count' => $anchor_count,
class-diagnostic-g anchor link behavior on mobile', 'wpshadow' ),
class-diagnostic-k' => 'https://wpshadow.com/kb/anchor-links',
class-diagnostic-);
class-diagnostic-}

class-diagnostic-private static function get_page_html(): ?string {
class-diagnostic-return Diagnostic_HTML_Helper::fetch_homepage_html(
class-diagnostic-	array(
class-diagnostic-		'timeout'   => 5,
class-diagnostic-		'sslverify' => false,
class-diagnostic-	)
class-diagnostic-);
class-diagnostic-}
}
