<?php
/**
 * Recovery Testing Diagnostic
 *
 * Analyzes backup restoration testing and verification.
 *
 * @since   1.6033.2150
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recovery Testing Diagnostic
 *
 * Evaluates backup restoration testing practices.
 *
 * @since 1.6033.2150
 */
class Diagnostic_Recovery_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'recovery-testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Recovery Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes backup restoration testing and verification';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2150
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup plugins
		$backup_plugins = array(
			'updraftplus/updraftplus.php'           => 'UpdraftPlus',
			'backwpup/backwpup.php'                 => 'BackWPup',
			'duplicator/duplicator.php'             => 'Duplicator',
			'jetpack/jetpack.php'                   => 'Jetpack Backup',
		);

		$active_backup = null;
		foreach ( $backup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup = $name;
				break;
			}
		}

		// Check for recovery test tracking
		$last_recovery_test = get_option( 'wpshadow_last_recovery_test' );
		$days_since_test = $last_recovery_test ? floor( ( time() - $last_recovery_test ) / DAY_IN_SECONDS ) : 999;

		// Check if site has staging environment
		$has_staging = false;
		if ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE === 'staging' ) {
			$has_staging = true;
		}

		// Check for staging plugins
		$staging_plugins = array(
			'wp-staging/wp-staging.php'             => 'WP Staging',
			'duplicator/duplicator.php'             => 'Duplicator',
		);

		$staging_plugin = null;
		foreach ( $staging_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$staging_plugin = $name;
				break;
			}
		}

		// Generate findings if no backup plugin
		if ( ! $active_backup ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No backup plugin detected. Cannot test recovery without backups.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/recovery-testing',
				'meta'         => array(
					'active_backup'  => $active_backup,
					'recommendation' => 'Install UpdraftPlus or BackWPup first',
				),
			);
		}

		// Alert if never tested recovery
		if ( $days_since_test > 180 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No record of backup restoration testing. Untested backups may fail when needed most.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/recovery-testing',
				'meta'         => array(
					'days_since_test'   => $days_since_test,
					'active_backup'     => $active_backup,
					'has_staging'       => $has_staging,
					'staging_plugin'    => $staging_plugin,
					'recommendation'    => 'Test backup restoration on staging site',
					'testing_importance' => array(
						'Verifies backup integrity',
						'Validates restoration process',
						'Estimates recovery time',
						'Identifies missing dependencies',
						'Builds confidence in disaster recovery',
					),
					'testing_frequency' => 'Quarterly for production sites',
					'testing_steps'     => array(
						'1. Create staging environment',
						'2. Download latest backup',
						'3. Restore to staging site',
						'4. Verify functionality',
						'5. Document recovery time',
						'6. Update disaster recovery plan',
					),
				),
			);
		}

		// Warning if no staging environment
		if ( ! $has_staging && ! $staging_plugin ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No staging environment detected. Staging sites enable safe backup testing.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/recovery-testing',
				'meta'         => array(
					'has_staging'    => $has_staging,
					'staging_plugin' => $staging_plugin,
					'recommendation' => 'Install WP Staging or create staging subdomain',
					'staging_benefits' => array(
						'Safe backup testing',
						'Update testing',
						'Plugin compatibility checks',
						'Development sandbox',
					),
				),
			);
		}

		return null;
	}
}
