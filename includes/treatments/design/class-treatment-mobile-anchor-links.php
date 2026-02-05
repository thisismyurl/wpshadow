<?php
/**
 * Mobile Anchor Link Performance
 *
 * Validates smooth scrolling and anchor link behavior on mobile.
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

class Treatment_Mobile_Anchor_Links extends Treatment_Base {

class-treatment-protected static $slug = 'mobile-anchor-link-performance';
class-treatment-protected static $title = 'Mobile Anchor Link Performance';
class-treatment-protected static $description = 'Validates anchor links for mobile';
class-treatment-protected static $family = 'navigation';

class-treatment-public static function check() {
class-treatment-$html = self::get_page_html();
class-treatment-if ( ! $html ) {
class-treatment- null;
class-treatment-}

class-treatment-$issues = array();

class-treatment-// Check for anchor links
class-treatment-$anchor_count = preg_match_all( '/href\s*=\s*["\']#[^"\']+["\']/i', $html, $matches );

class-treatment-if ( $anchor_count === 0 ) {
class-treatment- null; // No anchor links to check
class-treatment-}

class-treatment-// Check for smooth scroll CSS
class-treatment-preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
class-treatment-$css = implode( "\n", $style_matches[1] ?? array() );

class-treatment-$has_smooth_scroll = preg_match( '/scroll-behavior\s*:\s*smooth/i', $css );

class-treatment-if ( ! $has_smooth_scroll ) {
class-treatment-o-smooth-scroll',
class-treatment-chor links jump instantly (no smooth scrolling)',
class-treatment-\s*:\s*fixed.*?header|header.*?position\s*:\s*fixed/is', $css );

class-treatment-if ( $has_fixed_header ) {
class-treatment-g-top|scroll-margin-top/i', $css );

class-treatment-o-scroll-offset',
class-treatment-chor link targets',
class-treatment-dling
class-treatment-$has_js_scroll = preg_match( '/scrollIntoView|scrollTo\(|\.animate.*?scrollTop/i', $html );

class-treatment-if ( ! $has_js_scroll && ! $has_smooth_scroll ) {
class-treatment-o-scroll-implementation',
class-treatment-o smooth scroll implementation detected',
class-treatment- null;
class-treatment-}

class-treatment-return array(
class-treatment-' => sprintf( __( 'Found %d anchor link issues', 'wpshadow' ), count( $issues ) ),
class-treatment-chor_count' => $anchor_count,
class-treatment-g anchor link behavior on mobile', 'wpshadow' ),
class-treatment-k' => 'https://wpshadow.com/kb/anchor-links',
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
