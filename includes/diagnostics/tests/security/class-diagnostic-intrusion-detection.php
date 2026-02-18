<?php
/**
 * Intrusion Detection Diagnostic
 *
 * Analyzes file integrity and malware detection systems.
 *
 * @since   1.6033.2145
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
 * Intrusion Detection Diagnostic
 *
 * Evaluates file integrity monitoring and malware scanning.
 *
 * @since 1.6033.2145
 */
class Diagnostic_Intrusion_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'intrusion-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Intrusion Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes file integrity and malware detection systems';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2145
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for security scanning plugins
		$security_plugins = array(
			'wordfence/wordfence.php'           => 'Wordfence',
			'sucuri-scanner/sucuri.php'         => 'Sucuri Security',
			'antivirus/antivirus.php'           => 'AntiVirus',
			'quttera-web-malware-scanner/quttera-scanner.php' => 'Quttera',
		);

		$active_scanner = null;
		foreach ( $security_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_scanner = $name;
				break;
			}
		}

		// Check for recent malware scan
		$last_scan_time = get_option( 'wpshadow_last_malware_scan' );
		$days_since_scan = $last_scan_time ? floor( ( time() - $last_scan_time ) / DAY_IN_SECONDS ) : 999;

		// Check for file integrity monitoring
		$has_file_monitoring = false;
		if ( $active_scanner === 'Wordfence' ) {
			$has_file_monitoring = get_option( 'wordfence_filesIntegrityCheck' ) === '1';
		}

		// Check for suspicious files in common locations
		$suspicious_locations = array(
			WP_CONTENT_DIR . '/uploads/*.php',
			ABSPATH . 'wp-includes/*.php.suspected',
			get_template_directory() . '/*.suspected',
		);

		$suspicious_files = 0;
		foreach ( $suspicious_locations as $pattern ) {
			$files = glob( $pattern );
			if ( ! empty( $files ) ) {
				$suspicious_files += count( $files );
			}
		}

		// Generate findings if no scanning configured
		if ( ! $active_scanner ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No malware scanner or intrusion detection configured. Regular scans detect compromised files and backdoors.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/intrusion-detection',
				'meta'         => array(
					'active_scanner'     => $active_scanner,
					'has_file_monitoring' => $has_file_monitoring,
					'recommendation'     => 'Install Wordfence or Sucuri Security',
					'scan_features'      => array(
						'File integrity monitoring',
						'Malware signature detection',
						'Behavioral analysis',
						'Core file verification',
						'Plugin/theme vulnerability checks',
					),
					'detection_methods'  => array(
						'Known malware signatures',
						'Suspicious code patterns (base64_decode, eval)',
						'Modified core files',
						'Unexpected file creation',
						'Permission changes',
					),
				),
			);
		}

		// Alert if scan overdue
		if ( $days_since_scan > 7 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: days since last scan */
					__( 'Last malware scan was %d days ago. Run regular scans to detect intrusions early.', 'wpshadow' ),
					$days_since_scan
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/intrusion-detection',
				'meta'         => array(
					'days_since_scan'  => $days_since_scan,
					'active_scanner'   => $active_scanner,
					'recommendation'   => 'Schedule weekly automated scans',
					'scan_frequency'   => 'Daily for high-traffic sites, weekly minimum for all',
				),
			);
		}

		// Alert on suspicious files
		if ( $suspicious_files > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of suspicious files */
					__( '%d suspicious files detected. Review immediately for potential malware or backdoors.', 'wpshadow' ),
					$suspicious_files
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/intrusion-detection',
				'meta'         => array(
					'suspicious_files' => $suspicious_files,
					'recommendation'   => 'Run full malware scan and review flagged files',
					'immediate_action' => 'Site may be compromised - investigate urgently',
				),
			);
		}

		return null;
	}
}
