<?php
/**
 * Database Table Prefix Security Validation Diagnostic
 *
 * Ensures $table_prefix uses custom value, not default 'wp_'.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Table Prefix Security Validation Class
 *
 * Tests database table prefix security.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Database_Table_Prefix_Security_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-table-prefix-security-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Table Prefix Security Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures $table_prefix uses custom value, not default \'wp_\'';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		$prefix_check = self::check_table_prefix();
		
		if ( $prefix_check['has_concerns'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $prefix_check['concerns'] ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-table-prefix-security-validation',
				'meta'         => array(
					'table_prefix' => $prefix_check['prefix_redacted'],
				),
			);
		}

		return null;
	}

	/**
	 * Check database table prefix security.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_table_prefix() {
		global $wpdb;
		
		$check = array(
			'has_concerns'    => false,
			'concerns'        => array(),
			'prefix_redacted' => substr( $wpdb->prefix, 0, 3 ) . '***',
		);

		// Check for default prefix.
		if ( 'wp_' === $wpdb->prefix ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'Using default wp_ table prefix (no security-through-obscurity layer)', 'wpshadow' );
		}

		// Check for weak common prefixes.
		$weak_prefixes = array( 'wp1_', 'wordpress_', 'wpdb_', 'wp2_', 'blog_' );
		
		if ( in_array( $wpdb->prefix, $weak_prefixes, true ) ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = sprintf(
				/* translators: %s: table prefix */
				__( 'Table prefix "%s" is common and predictable', 'wpshadow' ),
				$wpdb->prefix
			);
		}

		// Check if prefix ends with underscore.
		if ( ! str_ends_with( $wpdb->prefix, '_' ) ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'Table prefix does not end with underscore (WordPress convention)', 'wpshadow' );
		}

		// Check for invalid characters.
		if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $wpdb->prefix ) ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'Table prefix contains invalid characters (only letters, numbers, underscore allowed)', 'wpshadow' );
		}

		return $check;
	}
}
