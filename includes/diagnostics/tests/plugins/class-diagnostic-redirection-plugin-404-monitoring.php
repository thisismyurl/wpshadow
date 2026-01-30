<?php
/**
 * Redirection Plugin 404 Monitoring Diagnostic
 *
 * Redirection Plugin 404 Monitoring issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1418.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirection Plugin 404 Monitoring Diagnostic Class
 *
 * @since 1.1418.0000
 */
class Diagnostic_RedirectionPlugin404Monitoring extends Diagnostic_Base {

	protected static $slug = 'redirection-plugin-404-monitoring';
	protected static $title = 'Redirection Plugin 404 Monitoring';
	protected static $description = 'Redirection Plugin 404 Monitoring issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'REDIRECTION_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: 404 logging enabled
		$log_404s = get_option( 'redirection_404_log', 0 );
		if ( ! $log_404s ) {
			$issues[] = '404 logging not enabled';
		}
		
		// Check 2: 404 log retention configured
		$retention_days = absint( get_option( 'redirection_404_retention_days', 0 ) );
		if ( $retention_days <= 0 ) {
			$issues[] = '404 log retention not configured';
		}
		
		// Check 3: 404 log limit configured
		$log_limit = absint( get_option( 'redirection_404_limit', 0 ) );
		if ( $log_limit <= 0 ) {
			$issues[] = '404 log limit not configured';
		}
		
		// Check 4: 404 ignore rules
		$ignore_rules = get_option( 'redirection_404_ignore', '' );
		if ( empty( $ignore_rules ) ) {
			$issues[] = '404 ignore rules not configured';
		}
		
		// Check 5: 404 cleanup enabled
		$cleanup_enabled = get_option( 'redirection_404_cleanup', 0 );
		if ( ! $cleanup_enabled ) {
			$issues[] = '404 cleanup not enabled';
		}
		
		// Check 6: 404 alerts configured
		$alerts_enabled = get_option( 'redirection_404_email_alerts', 0 );
		if ( ! $alerts_enabled ) {
			$issues[] = '404 alerts not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Redirection 404 monitoring issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/redirection-plugin-404-monitoring',
			);
		}
		
		return null;
	}
}
