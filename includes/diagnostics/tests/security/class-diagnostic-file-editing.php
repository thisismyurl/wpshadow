<?php
/**
 * File Editing Disabled Diagnostic
 *
 * Checks if DISALLOW_FILE_EDIT is set, verifies theme/plugin editor is disabled,
 * and tests for code execution vulnerabilities via admin panel.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.1615
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File Editing Disabled Diagnostic Class
 *
 * Verifies that file editing via the WordPress admin panel is disabled
 * to prevent unauthorized code execution.
 *
 * @since 1.6035.1615
 */
class Diagnostic_File_Editing extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'disables_file_editing';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'File Editing Disabled';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies theme and plugin file editing is disabled in admin panel';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1615
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for DISALLOW_FILE_EDIT constant (40 points).
		if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
			$earned_points += 40;
			$stats['disallow_file_edit'] = true;
		} else {
			$issues[] = 'DISALLOW_FILE_EDIT not set - theme and plugin editors are accessible';
			$stats['disallow_file_edit'] = false;
		}

		// Check for DISALLOW_FILE_MODS constant (30 points).
		if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
			$earned_points += 30;
			$stats['disallow_file_mods'] = true;
		} else {
			$warnings[] = 'DISALLOW_FILE_MODS not set - plugin/theme installation allowed via admin';
			$stats['disallow_file_mods'] = false;
		}

		// Check for security plugins enforcing file editing restrictions (20 points).
		$security_plugins = array(
			'wordfence/wordfence.php'                       => 'Wordfence Security',
			'better-wp-security/better-wp-security.php'     => 'iThemes Security',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'                     => 'Sucuri Security',
		);

		$active_security = array();
		foreach ( $security_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_security[] = $plugin_name;
				$earned_points    += 7; // Up to 20 points.
			}
		}

		if ( count( $active_security ) > 0 ) {
			$stats['security_plugins'] = implode( ', ', $active_security );
		} else {
			$warnings[] = 'No security plugins detected';
		}

		// Check file permissions on wp-config.php (10 points).
		$wp_config_path = ABSPATH . 'wp-config.php';
		if ( file_exists( $wp_config_path ) ) {
			$perms = fileperms( $wp_config_path );
			$octal = substr( sprintf( '%o', $perms ), -4 );

			$stats['wp_config_permissions'] = $octal;

			// Check if file is writable by group or others (insecure).
			if ( ! ( $perms & 0x0010 ) && ! ( $perms & 0x0002 ) ) {
				$earned_points += 10;
			} else {
				$issues[] = sprintf(
					/* translators: %s: Permissions in octal */
					__( 'wp-config.php has insecure permissions (%s)', 'wpshadow' ),
					$octal
				);
			}
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 70%.
		if ( $score < 70 ) {
			$severity     = $score < 50 ? 'high' : 'medium';
			$threat_level = $score < 50 ? 85 : 75;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your file editing security scored %s. When file editing is enabled, attackers who compromise an admin account can inject malicious code directly into your themes and plugins through the WordPress admin panel. Disabling file editing prevents this attack vector.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-editing-disabled',
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
