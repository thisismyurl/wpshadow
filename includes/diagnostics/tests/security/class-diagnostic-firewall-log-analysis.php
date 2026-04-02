<?php
/**
 * Firewall Log Analysis Diagnostic
 *
 * Analyzes firewall logs and blocked threats.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Firewall Log Analysis Diagnostic
 *
 * Evaluates firewall effectiveness and threat patterns.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Firewall_Log_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'firewall-log-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Firewall Log Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes firewall logs and blocked threats';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for firewall plugins with logging
		$firewall_plugins = array(
			'wordfence/wordfence.php'                   => 'Wordfence',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'                 => 'Sucuri Security',
			'better-wp-security/better-wp-security.php' => 'iThemes Security',
		);

		$active_firewall = null;
		foreach ( $firewall_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_firewall = $name;
				break;
			}
		}

		// Check Wordfence database tables for threat data (if available)
		global $wpdb;
		$threat_count = 0;
		$recent_blocks = 0;

		if ( $active_firewall === 'Wordfence' ) {
			$waf_table = $wpdb->prefix . 'wfHits';
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $waf_table ) ) === $waf_table ) {
				$threat_count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$waf_table} 
						WHERE action = 'blocked:waf' 
						AND attackLogTime > %d",
						time() - ( 7 * DAY_IN_SECONDS )
					)
				);
			}
		}

		// Check for login attempt blocking
		$login_log_table = $wpdb->prefix . 'wflogins';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $login_log_table ) ) === $login_log_table ) {
			$recent_blocks = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$login_log_table} 
					WHERE action = 'blocked' 
					AND ctime > %d",
					time() - ( 7 * DAY_IN_SECONDS )
				)
			);
		}

		// Generate findings based on firewall status
		if ( ! $active_firewall ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No web application firewall (WAF) detected. Firewalls prevent common attacks and log security threats.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/firewall-log-analysis',
				'meta'         => array(
					'active_firewall'   => $active_firewall,
					'recommendation'    => 'Install Wordfence or All In One WP Security',
					'firewall_benefits' => array(
						'Blocks SQL injection attempts',
						'Prevents XSS attacks',
						'Stops brute force login attempts',
						'Logs all security threats',
						'Rate limiting for abusive IPs',
					),
					'firewall_options'  => array(
						'Wordfence (free + premium)',
						'Sucuri (paid WAF service)',
						'Cloudflare WAF (paid)',
						'All In One WP Security (free)',
					),
				),
			);
		}

		// Alert on high threat activity
		if ( absint( $threat_count ) > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of blocked threats */
					__( '%d threats blocked in last 7 days. High attack volume detected - review firewall logs.', 'wpshadow' ),
					absint( $threat_count )
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/firewall-log-analysis',
				'meta'         => array(
					'threat_count'   => absint( $threat_count ),
					'recent_blocks'  => absint( $recent_blocks ),
					'active_firewall' => $active_firewall,
					'recommendation' => 'Review firewall logs and consider IP blocking',
					'attack_patterns' => 'Look for repeated IPs or attack signatures',
				),
			);
		}

		return null;
	}
}
