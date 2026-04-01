<?php
/**
 * PHP Max Execution Time Diagnostic
 *
 * Checks if PHP max execution time is sufficient for long-running operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PHP Max Execution Time Diagnostic Class
 *
 * Verifies PHP max execution time allows for imports, backups, and updates.
 * Think of it like a countdown timer—if it's too short, tasks get cut off.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Php_Max_Execution_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-max-execution-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Max Execution Time';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP max execution time is sufficient for long-running operations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the PHP max execution time diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if execution time issues detected, null otherwise.
	 */
	public static function check() {
		$max_execution_time = ini_get( 'max_execution_time' );

		// 0 means unlimited.
		if ( 0 == $max_execution_time ) {
			return null;
		}

		$min_recommended = 30;
		$preferred       = 60;
		$backup_recommended = 300;

		// Check for backup plugins.
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
		);

		$has_backup_plugin = false;
		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_backup_plugin = true;
				break;
			}
		}

		if ( $max_execution_time < $min_recommended ) {
			return array(
				'id'           => self::$slug . '-critical',
				'title'        => __( 'PHP Execution Time Too Short', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current execution time, 2: minimum recommended */
					__( 'Your PHP execution time limit is %1$s seconds, which is very short (like a countdown timer that ends too quickly). Operations like plugin updates, imports, or image processing may fail with timeout errors. Contact your hosting provider to increase this to at least %2$s seconds.', 'wpshadow' ),
					$max_execution_time,
					$min_recommended
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-max-execution-time?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'current_seconds' => $max_execution_time,
					'minimum'         => $min_recommended,
				),
			);
		}

		if ( $has_backup_plugin && $max_execution_time < $backup_recommended ) {
			return array(
				'id'           => self::$slug . '-backup',
				'title'        => __( 'Execution Time Low for Backups', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current execution time, 2: recommended for backups */
					__( 'Your PHP execution time is %1$s seconds. Backup operations often need %2$s seconds or more to complete (like needing extra time to copy large amounts of data). Large backups may fail with timeout errors. Consider asking your hosting provider to increase this limit.', 'wpshadow' ),
					$max_execution_time,
					$backup_recommended
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-max-execution-time?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'current_seconds'  => $max_execution_time,
					'recommended'      => $backup_recommended,
					'has_backup'       => true,
				),
			);
		}

		if ( $max_execution_time < $preferred ) {
			return array(
				'id'           => self::$slug . '-low',
				'title'        => __( 'PHP Execution Time Below Recommended', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current execution time, 2: recommended */
					__( 'Your PHP execution time is %1$s seconds. Increasing to %2$s seconds or more helps ensure imports, updates, and exports complete successfully (like giving yourself more time to finish a task). This is especially important for large content imports or bulk operations.', 'wpshadow' ),
					$max_execution_time,
					$preferred
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-max-execution-time?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'current_seconds' => $max_execution_time,
					'recommended'     => $preferred,
				),
			);
		}

		return null; // Execution time is adequate.
	}
}
