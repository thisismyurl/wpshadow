<?php
/**
 * Mobile Pagination UI
 *
 * Validates pagination controls for mobile touch interaction.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Navigation
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
class-diagnostic-exit;
}

class Diagnostic_Mobile_Pagination extends Diagnostic_Base {

class-diagnostic-protected static $slug = 'mobile-pagination-ui';
class-diagnostic-protected static $title = 'Mobile Pagination UI';
class-diagnostic-protected static $description = 'Validates pagination for mobile touch';
class-diagnostic-protected static $family = 'navigation';

class-diagnostic-public static function check() {
class-diagnostic-$html = self::get_page_html();
class-diagnostic-if ( ! $html ) {
class-diagnostic- null;
class-diagnostic-}

class-diagnostic-$issues = array();

class-diagnostic-// Check for pagination
class-diagnostic-$has_pagination = preg_match( '/class\s*=\s*["\'][^"\']*(?:pagination|paging|page-numbers)[^"\']*["\']/i', $html );

class-diagnostic-if ( ! $has_pagination ) {
class-diagnostic- null; // No pagination to check
class-diagnostic-}

class-diagnostic-// Extract pagination HTML
class-diagnostic-preg_match( '/<nav[^>]*class\s*=\s*["\'][^"\']*pagination[^"\']*["\'][^>]*>(.*?)<\/nav>/is', $html, $pagination_match );
class-diagnostic-$pagination_html = $pagination_match[1] ?? '';

class-diagnostic-// Check for tap target size (44px minimum)
class-diagnostic-preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
class-diagnostic-$css = implode( "\n", $style_matches[1] ?? array() );

class-diagnostic-if ( preg_match( '/\.pagination\s+a[^{]*{[^}]*(?:width|height)\s*:\s*([0-9]+)px/', $css, $size_match ) ) {
class-diagnostic-t) $size_match[1];
class-diagnostic-ation-too-small',
class-diagnostic-ation links smaller than 44px tap target',
class-diagnostic-ext links
class-diagnostic-$has_prev_next = preg_match( '/(?:prev|previous|next)/i', $pagination_html );

class-diagnostic-if ( ! $has_prev_next ) {
class-diagnostic-o-prev-next',
class-diagnostic-g prev/next navigation links',
class-diagnostic-ation|page)[^"\']*["\']/i', $pagination_html );

class-diagnostic-if ( ! $has_aria ) {
class-diagnostic-o-aria-labels',
class-diagnostic-ation missing aria-label for screen readers',
class-diagnostic- null;
class-diagnostic-}

class-diagnostic-return array(
class-diagnostic-' => sprintf( __( 'Found %d pagination issues', 'wpshadow' ), count( $issues ) ),
class-diagnostic-ation difficult to use on mobile', 'wpshadow' ),
class-diagnostic-k' => 'https://wpshadow.com/kb/pagination',
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
