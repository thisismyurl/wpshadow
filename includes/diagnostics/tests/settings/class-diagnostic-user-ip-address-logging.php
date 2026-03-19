<?php
/**
 * User IP Address Logging Diagnostic
 *
 * Checks for IP address logging and anonymization controls.
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
 * User IP Address Logging Diagnostic
 *
 * Validates IP logging practices and disclosure requirements.
 *
 * @since 1.6093.1200
 */
class Diagnostic_User_IP_Address_Logging extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-ip-address-logging';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User IP Address Logging';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for IP address logging and anonymization controls';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );
		$issues = array();
		$logging_sources = array();

		$ip_logging_plugins = array(
			'wordfence/wordfence.php' => 'Wordfence',
			'sucuri-scanner/sucuri.php' => 'Sucuri',
			'wp-security-audit-log/wp-security-audit-log.php' => 'WP Security Audit Log',
			'wp-statistics/wp-statistics.php' => 'WP Statistics',
		);

		foreach ( $ip_logging_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$logging_sources[] = $name;
			}
		}

		$anonymization = get_option( 'wpshadow_ip_anonymization_enabled', false );
		$privacy_page = get_option( 'wp_page_for_privacy_policy', 0 );

		if ( ! empty( $logging_sources ) && ! $anonymization ) {
			$issues[] = __( 'IP logging detected without anonymization controls', 'wpshadow' );
		}

		if ( ! empty( $logging_sources ) && 0 === (int) $privacy_page ) {
			$issues[] = __( 'Privacy policy page not configured for IP logging disclosure', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'IP address logging requires disclosure and anonymization', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-ip-address-logging',
				'details'      => array(
					'issues'          => $issues,
					'logging_sources' => $logging_sources,
					'anonymization'   => (bool) $anonymization,
				),
			);
		}

		return null;
	}
}
