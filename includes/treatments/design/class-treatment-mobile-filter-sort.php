<?php
/**
 * Mobile Filter/Sort Controls
 *
 * Validates product/content filtering on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Treatments\Navigation
 * @since      1.602.1450
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
class-treatment-exit;
}

class Treatment_Mobile_Filter_Sort extends Treatment_Base {

class-treatment-protected static $slug = 'mobile-filter-sort-controls';
class-treatment-protected static $title = 'Mobile Filter/Sort Controls';
class-treatment-protected static $description = 'Validates filters for mobile usability';
class-treatment-protected static $family = 'navigation';

class-treatment-public static function check() {
class-treatment-// Check if WooCommerce/EDD active
class-treatment-if ( ! class_exists( 'WooCommerce' ) && ! class_exists( 'Easy_Digital_Downloads' ) ) {
class-treatment- null; // Not applicable
class-treatment-}

class-treatment-$html = self::get_shop_page_html();
class-treatment-if ( ! $html ) {
class-treatment- null;
class-treatment-}

class-treatment-$issues = array();

class-treatment-// Check for filter widgets
class-treatment-$has_filters = preg_match( '/class\s*=\s*["\'][^"\']*(?:widget|filter|layered)[^"\']*["\']/i', $html );

class-treatment-if ( ! $has_filters ) {
class-treatment- null; // No filters to check
class-treatment-}

class-treatment-// Check for mobile-optimized filter UI
class-treatment-$has_mobile_filter = preg_match( '/@media.*?max-width.*?(?:filter|widget)/i', $html );

class-treatment-if ( ! $has_mobile_filter ) {
class-treatment-o-mobile-filter-styles',
class-treatment-ot optimized for mobile viewport',
class-treatment- selects (better for mobile)
class-treatment-$has_select = preg_match( '/<select[^>]*class\s*=\s*["\'][^"\']*(?:filter|sort)[^"\']*["\']/i', $html );

class-treatment-if ( ! $has_select ) {
class-treatment-o-select-filters',
class-treatment-g checkboxes instead of mobile-friendly dropdowns',
class-treatment-
class-treatment-$has_apply_button = preg_match( '/<button[^>]*class\s*=\s*["\'][^"\']*(?:apply|filter)[^"\']*["\']/i', $html );

class-treatment-if ( ! $has_apply_button ) {
class-treatment-o-apply-button',
class-treatment-g clear "Apply Filters" button',
class-treatment- null;
class-treatment-}

class-treatment-return array(
class-treatment-' => sprintf( __( 'Found %d filter/sort issues', 'wpshadow' ), count( $issues ) ),
class-treatment-g difficult on mobile', 'wpshadow' ),
class-treatment-k' => 'https://wpshadow.com/kb/mobile-filters',
class-treatment-);
class-treatment-}

class-treatment-private static function get_shop_page_html(): ?string {
class-treatment-if ( class_exists( 'WooCommerce' ) ) {
class-treatment-k( wc_get_page_id( 'shop' ) );
class-treatment-} else {
class-treatment-se = wp_remote_get( $shop_url, array( 'timeout' => 5, 'sslverify' => false ) );
class-treatment-if ( is_wp_error( $response ) ) {
class-treatment- null;
class-treatment-}
class-treatment-return wp_remote_retrieve_body( $response );
class-treatment-}
}
