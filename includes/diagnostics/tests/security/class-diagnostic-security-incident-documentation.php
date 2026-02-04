<?php
/**
 * Security Incident Documentation Diagnostic
 *
 * Tests if security incidents are logged and tracked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1524
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Incident Documentation Diagnostic Class
 *
 * Evaluates whether security incidents are properly logged, tracked, and documented.
 * Checks for logging systems, incident response tools, and audit trails.
 *
 * @since 1.6035.1524
 */
class Diagnostic_Security_Incident_Documentation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'documents_security_incidents';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Incident Documentation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if security incidents are logged and tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1524
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Check for security logging plugins.
		$total_points += 30;
		$security_log_plugins = array(
			'wp-security-audit-log/wp-security-audit-log.php' => 'WP Activity Log',
			'simple-history/index.php'                        => 'Simple History',
			'stream/stream.php'                               => 'Stream',
			'audit-trail/audit-trail.php'                     => 'Audit Trail',
			'wp-log-viewer/wp-log-viewer.php'                 => 'WP Log Viewer',
		);

		$active_log_plugins = array();
		foreach ( $security_log_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_log_plugins[] = $name;
			}
		}

		if ( ! empty( $active_log_plugins ) ) {
			$earned_points += 30;
		}

		$stats['security_logging'] = array(
			'found' => count( $active_log_plugins ),
			'list'  => $active_log_plugins,
		);

		if ( empty( $active_log_plugins ) ) {
			$issues[] = __( 'No security logging plugin detected', 'wpshadow' );
		}

		// Check for security monitoring plugins.
		$total_points += 25;
		$monitoring_plugins = array(
			'wordfence/wordfence.php'         => 'Wordfence',
			'ithemes-security-pro/ithemes-security-pro.php' => 'iThemes Security Pro',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'       => 'Sucuri Security',
			'jetpack/jetpack.php'             => 'Jetpack (includes security monitoring)',
		);

		$active_monitoring = array();
		foreach ( $monitoring_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_monitoring[] = $name;
			}
		}

		if ( ! empty( $active_monitoring ) ) {
			$earned_points += 25;
		}

		$stats['security_monitoring'] = array(
			'found' => count( $active_monitoring ),
			'list'  => $active_monitoring,
		);

		if ( empty( $active_monitoring ) ) {
			$issues[] = __( 'No security monitoring plugin detected', 'wpshadow' );
		}

		// Check for backup plugins (incident recovery).
		$total_points += 15;
		$backup_plugins = array(
			'updraftplus/updraftplus.php'                   => 'UpdraftPlus',
			'backwpup/backwpup.php'                         => 'BackWPup',
			'duplicator/duplicator.php'                     => 'Duplicator',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'jetpack/jetpack.php'                           => 'Jetpack (includes backups)',
		);

		$active_backup_plugins = array();
		foreach ( $backup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup_plugins[] = $name;
			}
		}

		if ( ! empty( $active_backup_plugins ) ) {
			$earned_points += 15;
		}

		$stats['backup_plugins'] = array(
			'found' => count( $active_backup_plugins ),
			'list'  => $active_backup_plugins,
		);

		if ( empty( $active_backup_plugins ) ) {
			$warnings[] = __( 'No backup plugin detected (critical for incident recovery)', 'wpshadow' );
		}

		// Check for incident response documentation.
		$total_points += 15;
		$incident_keywords = array( 'security incident', 'incident response', 'security breach', 'security event' );
		$incident_docs     = array();

		foreach ( $incident_keywords as $keyword ) {
			$docs = get_posts(
				array(
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 5,
					'post_status'    => 'any',
					's'              => $keyword,
				)
			);
			$incident_docs = array_merge( $incident_docs, $docs );
		}

		$incident_docs = array_unique( $incident_docs, SORT_REGULAR );
		$stats['incident_documentation'] = count( $incident_docs );

		if ( count( $incident_docs ) >= 2 ) {
			$earned_points += 15;
		} elseif ( count( $incident_docs ) === 1 ) {
			$earned_points += 10;
		} else {
			$warnings[] = __( 'No incident response documentation found', 'wpshadow' );
		}

		// Check for email notification capabilities.
		$total_points += 10;
		// Most security plugins provide email notifications.
		if ( ! empty( $active_monitoring ) || ! empty( $active_log_plugins ) ) {
			$earned_points += 10;
			$stats['notification_capability'] = true;
		} else {
			$stats['notification_capability'] = false;
			$warnings[] = __( 'No security notification system detected', 'wpshadow' );
		}

		// Check for WordPress debug logging.
		$total_points += 5;
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$earned_points += 5;
			$stats['wp_debug_log_enabled'] = true;
		} else {
			$stats['wp_debug_log_enabled'] = false;
		}

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'high';
		$threat_level = 60;

		if ( $score < 40 ) {
			$severity     = 'high';
			$threat_level = 65;
		} elseif ( $score >= 40 && $score < 70 ) {
			$severity     = 'medium';
			$threat_level = 50;
		} else {
			$severity     = 'low';
			$threat_level = 30;
		}

		// Return finding if incident documentation is insufficient.
		if ( $score < 70 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: incident documentation score percentage */
					__( 'Security incident documentation score: %d%%. Proper logging and tracking of security incidents is critical for compliance and response.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-incident-documentation',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
