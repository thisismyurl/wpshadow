<?php
/**
 * WP Constants Duplicated Diagnostic
 *
 * Detects duplicate constant definitions in wp-config.php.
 *
 * @since   1.2601.2112
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Wp_Constants_Duplicated
 *
 * Checks wp-config.php for duplicate constant definitions that could cause configuration conflicts.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Wp_Constants_Duplicated extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2112
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		$config_file = ABSPATH . 'wp-config.php';

		if ( ! is_readable( $config_file ) ) {
			return null;
		}

		$duplicates = self::find_duplicate_defines( $config_file );

		if ( ! empty( $duplicates ) ) {
			return array(
				'id'           => 'wp-constants-duplicated',
				'title'        => __( 'Duplicate Constants in wp-config.php', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of constants */
					__( 'Found duplicate define() statements for: %s. Only the first definition is used. Remove redundant definitions to avoid confusion.', 'wpshadow' ),
					implode( ', ', array_slice( array_keys( $duplicates ), 0, 5 ) ) . ( count( $duplicates ) > 5 ? ' +' . ( count( $duplicates ) - 5 ) . ' more' : '' )
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp_constants_duplicated',
				'meta'         => array(
					'duplicate_count' => count( $duplicates ),
					'constants'       => array_keys( $duplicates ),
				),
			);
		}

		return null;
	}

	/**
	 * Find duplicate define() statements in wp-config.php.
	 *
	 * @since  1.2601.2112
	 * @param  string $file File path.
	 * @return array Associative array of duplicates.
	 */
	private static function find_duplicate_defines( $file ) {
		$content = file_get_contents( $file );
		if ( false === $content ) {
			return array();
		}

		$duplicates = array();
		$found      = array();

		// Simple regex to find define() calls: define( 'CONSTANT_NAME', ... ).
		if ( preg_match_all( "/define\s*\(\s*['\"]([^'\"]+)['\"]\s*,/", $content, $matches ) ) {
			foreach ( $matches[1] as $constant ) {
				if ( isset( $found[ $constant ] ) ) {
					$duplicates[ $constant ] = true;
				}
				$found[ $constant ] = true;
			}
		}

		return $duplicates;
	}
}
