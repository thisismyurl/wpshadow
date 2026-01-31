<?php
/**
 * Diagnostic: PHP OPCache Blacklist
 *
 * Checks if PHP OPcache blacklist is configured for files that shouldn't be cached.
 * Certain files (like admin pages) should be excluded from OPcache.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Opcache_Blacklist
 *
 * Tests PHP OPcache blacklist configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Opcache_Blacklist extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-opcache-blacklist';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP OPCache Blacklist';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP OPcache blacklist is configured';

	/**
	 * Check PHP OPcache blacklist.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if OPcache extension is loaded.
		if ( ! extension_loaded( 'Zend OPcache' ) && ! extension_loaded( 'opcache' ) ) {
			return null; // Not applicable if OPcache not available.
		}

		// Check if OPcache is enabled.
		if ( ! function_exists( 'opcache_get_status' ) ) {
			return null; // Can't check if function not available.
		}

		$status = opcache_get_status( false );

		if ( ! $status || ! isset( $status['opcache_enabled'] ) || ! $status['opcache_enabled'] ) {
			return null; // Not applicable if OPcache not enabled.
		}

		// Check if blacklist file is configured.
		$blacklist_file = ini_get( 'opcache.blacklist_filename' );

		if ( empty( $blacklist_file ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP OPcache blacklist is not configured. Consider creating a blacklist file to exclude frequently-changing files (like wp-config.php) from caching.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_opcache_blacklist',
				'meta'        => array(
					'opcache_enabled'   => true,
					'blacklist_enabled' => false,
				),
			);
		}

		// Check if blacklist file exists.
		if ( ! file_exists( $blacklist_file ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Blacklist file path */
					__( 'PHP OPcache blacklist file is configured (%s) but does not exist. Create this file with paths to exclude from caching.', 'wpshadow' ),
					$blacklist_file
				),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_opcache_blacklist',
				'meta'        => array(
					'opcache_enabled'   => true,
					'blacklist_enabled' => true,
					'blacklist_file'    => $blacklist_file,
					'file_exists'       => false,
				),
			);
		}

		// Check if blacklist file is readable and has entries.
		if ( is_readable( $blacklist_file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$blacklist_contents = file_get_contents( $blacklist_file );
			$blacklist_entries  = array_filter( explode( "\n", $blacklist_contents ), 'trim' );

			// Remove comments.
			$blacklist_entries = array_filter(
				$blacklist_entries,
				function ( $line ) {
					return ! empty( $line ) && strpos( trim( $line ), '#' ) !== 0 && strpos( trim( $line ), ';' ) !== 0;
				}
			);

			if ( empty( $blacklist_entries ) ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'PHP OPcache blacklist file exists but is empty. Add file paths or patterns to exclude from caching.', 'wpshadow' ),
					'severity'    => 'info',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_opcache_blacklist',
					'meta'        => array(
						'opcache_enabled'   => true,
						'blacklist_enabled' => true,
						'blacklist_file'    => $blacklist_file,
						'file_exists'       => true,
						'entry_count'       => 0,
					),
				);
			}
		}

		// PHP OPcache blacklist is properly configured.
		return null;
	}
}
