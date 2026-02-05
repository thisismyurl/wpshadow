<?php
/**
 * Failed Login Attempts Treatment
 *
 * Detects unusual failed login activity or missing login monitoring.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1335
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Failed_Login_Attempts Class
 *
 * Checks for excessive failed login attempts or lack of monitoring.
 *
 * @since 1.6035.1335
 */
class Treatment_Failed_Login_Attempts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'failed-login-attempts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Failed Login Attempts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive failed login attempts';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$monitoring_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'wordfence/wordfence.php',
			'wp-fail2ban/wp-fail2ban.php',
		);

		$has_monitoring = false;
		foreach ( $monitoring_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_monitoring = true;
				break;
			}
		}

		$failed_count = (int) get_option( 'wpshadow_failed_logins_24h', 0 );

		if ( $failed_count >= 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'High number of failed login attempts detected in the last 24 hours.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/failed-login-attempts',
				'meta'         => array(
					'failed_logins_24h' => $failed_count,
				),
			);
		}

		if ( 0 === $failed_count && ! $has_monitoring ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Failed login attempts are not being monitored. Enable a login security plugin to detect brute-force activity.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/failed-login-attempts',
				'meta'         => array(
					'failed_logins_24h' => $failed_count,
					'has_monitoring'    => $has_monitoring,
				),
			);
		}

		return null;
	}
}