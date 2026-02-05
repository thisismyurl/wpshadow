<?php
/**
 * Mobile Pagination UI
 *
 * Validates pagination controls for mobile touch interaction.
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

class Treatment_Mobile_Pagination extends Treatment_Base {

class-treatment-protected static $slug = 'mobile-pagination-ui';
class-treatment-protected static $title = 'Mobile Pagination UI';
class-treatment-protected static $description = 'Validates pagination for mobile touch';
class-treatment-protected static $family = 'navigation';

class-treatment-public static function check() {
class-treatment-$html = self::get_page_html();
class-treatment-if ( ! $html ) {
class-treatment- null;
class-treatment-}

class-treatment-$issues = array();

class-treatment-// Check for pagination
class-treatment-$has_pagination = preg_match( '/class\s*=\s*["\'][^"\']*(?:pagination|paging|page-numbers)[^"\']*["\']/i', $html );

class-treatment-if ( ! $has_pagination ) {
class-treatment- null; // No pagination to check
class-treatment-}

class-treatment-// Extract pagination HTML
class-treatment-preg_match( '/<nav[^>]*class\s*=\s*["\'][^"\']*pagination[^"\']*["\'][^>]*>(.*?)<\/nav>/is', $html, $pagination_match );
class-treatment-$pagination_html = $pagination_match[1] ?? '';

class-treatment-// Check for tap target size (44px minimum)
class-treatment-preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
class-treatment-$css = implode( "\n", $style_matches[1] ?? array() );

class-treatment-if ( preg_match( '/\.pagination\s+a[^{]*{[^}]*(?:width|height)\s*:\s*([0-9]+)px/', $css, $size_match ) ) {
class-treatment-t) $size_match[1];
class-treatment-ation-too-small',
class-treatment-ation links smaller than 44px tap target',
class-treatment-ext links
class-treatment-$has_prev_next = preg_match( '/(?:prev|previous|next)/i', $pagination_html );

class-treatment-if ( ! $has_prev_next ) {
class-treatment-o-prev-next',
class-treatment-g prev/next navigation links',
class-treatment-ation|page)[^"\']*["\']/i', $pagination_html );

class-treatment-if ( ! $has_aria ) {
class-treatment-o-aria-labels',
class-treatment-ation missing aria-label for screen readers',
class-treatment- null;
class-treatment-}

class-treatment-return array(
class-treatment-' => sprintf( __( 'Found %d pagination issues', 'wpshadow' ), count( $issues ) ),
class-treatment-ation difficult to use on mobile', 'wpshadow' ),
class-treatment-k' => 'https://wpshadow.com/kb/pagination',
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
