<?php
/**
 * Mobile Filter/Sort Controls
 *
 * Validates product/content filtering on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Navigation
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
class-diagnostic-exit;
}

class Diagnostic_Mobile_Filter_Sort extends Diagnostic_Base {

class-diagnostic-protected static $slug = 'mobile-filter-sort-controls';
class-diagnostic-protected static $title = 'Mobile Filter/Sort Controls';
class-diagnostic-protected static $description = 'Validates filters for mobile usability';
class-diagnostic-protected static $family = 'navigation';

class-diagnostic-public static function check() {
class-diagnostic-// Check if WooCommerce/EDD active
class-diagnostic-if ( ! class_exists( 'WooCommerce' ) && ! class_exists( 'Easy_Digital_Downloads' ) ) {
class-diagnostic- null; // Not applicable
class-diagnostic-}

class-diagnostic-$html = self::get_shop_page_html();
class-diagnostic-if ( ! $html ) {
class-diagnostic- null;
class-diagnostic-}

class-diagnostic-$issues = array();

class-diagnostic-// Check for filter widgets
class-diagnostic-$has_filters = preg_match( '/class\s*=\s*["\'][^"\']*(?:widget|filter|layered)[^"\']*["\']/i', $html );

class-diagnostic-if ( ! $has_filters ) {
class-diagnostic- null; // No filters to check
class-diagnostic-}

class-diagnostic-// Check for mobile-optimized filter UI
class-diagnostic-$has_mobile_filter = preg_match( '/@media.*?max-width.*?(?:filter|widget)/i', $html );

class-diagnostic-if ( ! $has_mobile_filter ) {
class-diagnostic-o-mobile-filter-styles',
class-diagnostic-ot optimized for mobile viewport',
class-diagnostic- selects (better for mobile)
class-diagnostic-$has_select = preg_match( '/<select[^>]*class\s*=\s*["\'][^"\']*(?:filter|sort)[^"\']*["\']/i', $html );

class-diagnostic-if ( ! $has_select ) {
class-diagnostic-o-select-filters',
class-diagnostic-g checkboxes instead of mobile-friendly dropdowns',
class-diagnostic-
class-diagnostic-$has_apply_button = preg_match( '/<button[^>]*class\s*=\s*["\'][^"\']*(?:apply|filter)[^"\']*["\']/i', $html );

class-diagnostic-if ( ! $has_apply_button ) {
class-diagnostic-o-apply-button',
class-diagnostic-g clear "Apply Filters" button',
class-diagnostic- null;
class-diagnostic-}

class-diagnostic-return array(
class-diagnostic-' => sprintf( __( 'Found %d filter/sort issues', 'wpshadow' ), count( $issues ) ),
class-diagnostic-g difficult on mobile', 'wpshadow' ),
class-diagnostic-k' => 'https://wpshadow.com/kb/mobile-filters',
class-diagnostic-);
class-diagnostic-}

class-diagnostic-private static function get_shop_page_html(): ?string {
class-diagnostic-if ( class_exists( 'WooCommerce' ) ) {
class-diagnostic-k( wc_get_page_id( 'shop' ) );
class-diagnostic-} else {
class-diagnostic-se = wp_remote_get( $shop_url, array( 'timeout' => 5, 'sslverify' => false ) );
class-diagnostic-if ( is_wp_error( $response ) ) {
class-diagnostic- null;
class-diagnostic-}
class-diagnostic-return wp_remote_retrieve_body( $response );
class-diagnostic-}
}
