<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Registry Bloat (WP-335)
 *
 * Detects unused/enqueued block scripts/styles inflating payload.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_BlockRegistryBloat extends Diagnostic_Base {
	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$unused_blocks     = (int) get_transient( 'wpshadow_unused_block_assets' );
		$block_asset_bytes = (int) get_transient( 'wpshadow_block_asset_bytes' );

		if ( $unused_blocks > 0 && $block_asset_bytes > 0 ) {
			return array(
				'id'            => 'block-registry-bloat',
				'title'         => sprintf( __( 'Unused block assets detected (%d blocks)', 'wpshadow' ), $unused_blocks ),
				'description'   => __( 'Block scripts/styles are enqueued but unused on this page. Deregister unused blocks or enable selective asset loading.', 'wpshadow' ),
				'severity'      => 'medium',
				'category'      => 'other',
				'kb_link'       => 'https://wpshadow.com/kb/block-registry-bloat/',
				'training_link' => 'https://wpshadow.com/training/block-asset-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'asset_bytes'   => $block_asset_bytes,
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
