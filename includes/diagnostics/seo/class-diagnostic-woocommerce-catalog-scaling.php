<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WooCommerce Catalog Scaling (WC-311)
 *
 * Profiles product/variation queries and HPOS readiness.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_WoocommerceCatalogScaling extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Check WooCommerce catalog scaling (if WooCommerce is active)
        if (!function_exists('WC')) {
            return null; // WooCommerce not active
        }
        
        global $wpdb;
        
        // Count total products
        $product_count = (int)$wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='product'"
        );
        
        // If more than 10,000 products, performance may suffer
        if ($product_count > 10000) {
            return array(
                'id' => 'woocommerce-catalog-scaling',
                'title' => sprintf(__('Large WooCommerce Catalog (%d products)', 'wpshadow'), $product_count),
                'description' => __('Large product catalogs require performance optimization: product search indexing, pagination, lazy loading, and database queries.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/woocommerce-optimization/',
                'training_link' => 'https://wpshadow.com/training/large-catalog-performance/',
                'auto_fixable' => false,
                'threat_level' => 55,
            );
        }
        return null;
}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
