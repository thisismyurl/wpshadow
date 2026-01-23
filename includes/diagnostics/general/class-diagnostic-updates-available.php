<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Updates Waiting to Install?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Updates_Available extends Diagnostic_Base {
	protected static $slug        = 'updates-available';
	protected static $title       = 'Updates Waiting to Install?';
	protected static $description = 'Lists WordPress, plugin, and theme updates available.';

	public static function check(): ?array {
		$core_updates   = get_core_updates();
		$plugin_updates = get_plugin_updates();
		$theme_updates  = get_theme_updates();

		$core_count   = ( isset( $core_updates[0] ) && $core_updates[0]->response === 'upgrade' ) ? 1 : 0;
		$plugin_count = count( $plugin_updates );
		$theme_count  = count( $theme_updates );
		$total        = $core_count + $plugin_count + $theme_count;

		if ( $total === 0 ) {
			return null;
		}

		$parts = array();
		if ( $core_count > 0 ) {
			$parts[] = __( 'WordPress core', 'wpshadow' );
		}
		if ( $plugin_count > 0 ) {
			$parts[] = sprintf( _n( '%d plugin', '%d plugins', $plugin_count, 'wpshadow' ), $plugin_count );
		}
		if ( $theme_count > 0 ) {
			$parts[] = sprintf( _n( '%d theme', '%d themes', $theme_count, 'wpshadow' ), $theme_count );
		}

		return array(
			'id'            => static::$slug,
			'title'         => sprintf( _n( '%d update available', '%d updates available', $total, 'wpshadow' ), $total ),
			'description'   => sprintf(
				__( 'Updates waiting: %s. Keeping your site updated improves security and performance.', 'wpshadow' ),
				implode( ', ', $parts )
			),
			'severity'      => 'medium',
			'category'      => 'wordpress-config',
			'kb_link'       => 'https://wpshadow.com/kb/updates-available/',
			'training_link' => 'https://wpshadow.com/training/updates-available/',
			'auto_fixable'  => false,
			'threat_level'  => 50,
			'update_counts' => array(
				'core'    => $core_count,
				'plugins' => $plugin_count,
				'themes'  => $theme_count,
			),
		);
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
