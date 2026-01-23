<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: wp_postmeta Index Usage (DB-016)
 * 
 * Analyzes if postmeta queries use indexes efficiently.
 * Philosophy: Ridiculously good (#7) - deep analysis for free.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Postmeta_Index_Usage extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check if postmeta indexes are being used effectively
        global $wpdb;
        
        // Get info about postmeta table indexes
        $postmeta_info = $wpdb->get_results(
            "SHOW INDEX FROM {$wpdb->postmeta} WHERE Key_name != 'PRIMARY'"
        );
        
        if (count($postmeta_info) < 2) {
            return array(
                'id' => 'postmeta-index-usage',
                'title' => __('Postmeta Indexes May Be Insufficient', 'wpshadow'),
                'description' => __('Add indexes to postmeta columns (meta_key, post_id) to speed up custom field queries. Work with your hosting provider.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/database-indexing/',
                'training_link' => 'https://wpshadow.com/training/postmeta-optimization/',
                'auto_fixable' => false,
                'threat_level' => 45,
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
