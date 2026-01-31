<?php
/**
 * WordPress Memory Allocation Diagnostic
 *
 * Verifies PHP memory limit is adequate for WordPress operations
 * to prevent site crashes and functionality issues.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_WordPress_Memory_Allocation Class
 *
 * Verifies WordPress has adequate PHP memory allocation.
 *
 * @since 1.2601.2148
 */
class Diagnostic_WordPress_Memory_Allocation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-memory-allocation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Memory Allocation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies PHP memory limit is adequate';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'infrastructure';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if memory issue found, null otherwise.
	 */
	public static function check() {
		$memory_status = self::check_memory_status();

		if ( $memory_status['is_adequate'] ) {
			return null; // Memory is adequate
		}

		$severity = $memory_status['memory_mb'] < 64 ? 'critical' : 'high';
		$threat   = $memory_status['memory_mb'] < 64 ? 85 : 70;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: current memory limit in MB */
				__( 'PHP memory limit is too low (%dMB). WordPress should have at least 256MB. Plugins will fail, site may crash.', 'wpshadow' ),
				$memory_status['memory_mb']
			),
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/php-memory-limit',
			'family'       => self::$family,
			'meta'         => array(
				'current_limit'   => $memory_status['current_display'],
				'recommended'     => '256MB - 512MB',
				'for_woocommerce' => '512MB - 1GB',
				'impact'          => __( 'Low memory = plugins fail, uploads broken, admin slow' ),
			),
			'details'      => array(
				'memory_thresholds'      => array(
					'< 64MB (Critical)' => array(
						'WordPress crashes frequently',
						'Plugins fail to load',
						'Admin panel times out',
						'Immediately increase to at least 128MB',
					),
					'64-128MB (Warning)' => array(
						'Marginal, plugins may fail',
						'Site slow under load',
						'Increase to at least 256MB',
					),
					'128-256MB (Acceptable)' => array(
						'Minimum for basic WordPress',
						'Some plugins may hit limits',
						'Recommend 256MB for comfort',
					),
					'256-512MB (Recommended)' => array(
						'Ideal for most sites',
						'Handles plugins and heavy operations',
						'WooCommerce minimum',
					),
					'512MB+ (Optimal)' => array(
						'Large sites, many plugins',
						'E-commerce with many products',
						'Sites with batch operations',
					),
				),
				'memory_hogs'            => array(
					'WooCommerce' => '200MB+ (lots of product data)',
					'Elementor' => '100MB+ (page builder)',
					'Backups (UpdraftPlus)' => '500MB+ (full site backup)',
					'Image processing' => '200MB+ (thumbnail generation)',
					'Large imports' => '1GB+ (CSV imports)',
				),
				'increasing_memory'      => array(
					'Method 1: wp-config.php (Most Common)' => array(
						'1. Download wp-config.php via FTP',
						'2. Find line: define("WP_MEMORY_LIMIT", "40M");',
						'3. Change to: define("WP_MEMORY_LIMIT", "256M");',
						'4. For admin: define("WP_MEMORY_LIMIT_FOR_ADMIN", "256M");',
						'5. Upload file back',
						'6. Test WordPress admin',
					),
					'Method 2: .htaccess (Apache)' => array(
						'1. Add to .htaccess file:',
						'php_value memory_limit 256M',
						'2. Upload and test',
						'3. Verify in wp-admin → Tools → Site Health',
					),
					'Method 3: Hosting Control Panel' => array(
						'1. Log into cPanel/Plesk',
						'2. Find PHP Configuration or MultiPHP Manager',
						'3. Select your PHP version',
						'4. Find memory_limit setting',
						'5. Change to 256M or higher',
						'6. Apply changes',
					),
					'Method 4: Hosting Provider (Safest)' => array(
						'1. Contact hosting support',
						'2. Request memory limit increase to 256MB',
						'3. Provide them wp-admin URL',
						'4. They handle it safely',
					),
				),
				'testing_after_change'   => array(
					'1. Go to wp-admin → Tools → Site Health',
					'2. Check "PHP Memory Limit" shows new value',
					'3. Test plugin heavy operations',
					'4. Test large file uploads',
					'5. Create a full backup',
				),
				'wordpress_core_changes' => array(
					'If using wp-config.php method:',
					'- Changes survive WordPress updates',
					'- Place before "/* That\'s all, stop editing! */"',
					'- Test after each WordPress update',
				),
			),
		);
	}

	/**
	 * Check memory status.
	 *
	 * @since  1.2601.2148
	 * @return array Memory status information.
	 */
	private static function check_memory_status() {
		$limit = WP_MEMORY_LIMIT;

		// Convert to MB
		$memory_mb = self::convert_to_mb( $limit );

		return array(
			'memory_mb'        => $memory_mb,
			'current_display'  => $limit,
			'is_adequate'      => $memory_mb >= 256,
		);
	}

	/**
	 * Convert memory limit to MB.
	 *
	 * @since  1.2601.2148
	 * @param  string $value Memory value (e.g., '256M', '1G').
	 * @return int Memory in MB.
	 */
	private static function convert_to_mb( $value ) {
		$value  = trim( $value );
		$last   = strtoupper( substr( $value, -1 ) );
		$number = (int) substr( $value, 0, -1 );

		switch ( $last ) {
			case 'G':
				$number *= 1024;
				break;
			case 'M':
				break;
			case 'K':
				$number = (int) ( $number / 1024 );
				break;
		}

		return $number;
	}
}
