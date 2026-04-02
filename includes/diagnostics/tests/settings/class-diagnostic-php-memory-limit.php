<?php
/**
 * PHP Memory Limit Diagnostic
 *
 * Checks whether PHP memory limit is sufficient for typical WordPress operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_PHP_Memory_Limit Class
 *
 * Verifies PHP memory limit against recommended thresholds.
 *
 * @since 1.6093.1200
 */
class Diagnostic_PHP_Memory_Limit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-memory-limit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Memory Limit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP memory limit is sufficient for WordPress';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$raw_limit = ini_get( 'memory_limit' );
		$limit     = self::parse_size( $raw_limit );
		$display   = $raw_limit ? $raw_limit : __( 'unknown', 'wpshadow' );

		if ( $limit > 0 && $limit < 64 * 1024 * 1024 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: memory limit */
					__( 'PHP memory limit is %s. Recommended minimum is 128M for stable operations.', 'wpshadow' ),
					esc_html( $display )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-memory-limit',
				'meta'         => array(
					'memory_limit' => $display,
				),
			);
		}

		if ( $limit > 0 && $limit < 128 * 1024 * 1024 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: memory limit */
					__( 'PHP memory limit is %s. Consider raising to 256M for best results.', 'wpshadow' ),
					esc_html( $display )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-memory-limit',
				'meta'         => array(
					'memory_limit' => $display,
				),
			);
		}

		return null;
	}

	/**
	 * Parse size string like 128M into bytes.
	 *
	 * @since 1.6093.1200
	 * @param  string|false $value Memory limit string.
	 * @return int Size in bytes.
	 */
	private static function parse_size( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return 0;
		}

		$value = trim( $value );
		if ( '-1' === $value ) {
			return PHP_INT_MAX;
		}

		$unit  = strtoupper( substr( $value, -1 ) );
		$bytes = (int) $value;

		switch ( $unit ) {
			case 'G':
				$bytes *= 1024;
				// no break
			case 'M':
				$bytes *= 1024;
				// no break
			case 'K':
				$bytes *= 1024;
		}

		return $bytes;
	}
}