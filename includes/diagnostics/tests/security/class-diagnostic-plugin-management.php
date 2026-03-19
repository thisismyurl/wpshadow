<?php
/**
 * Plugin Management Strategy Diagnostic
 *
 * Tests if unnecessary plugins are regularly audited and removed.
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
 * Plugin Management Diagnostic Class
 *
 * Evaluates whether plugins are managed strategically with regular audits
 * and removal of unused/unnecessary plugins.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_Management extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'manages_plugins_strategically';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Management Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if unnecessary plugins are regularly audited and removed';

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
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Get all plugins.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		$total_plugins    = count( $all_plugins );
		$active_count     = count( $active_plugins );
		$inactive_count   = $total_plugins - $active_count;

		$stats['total_plugins']    = $total_plugins;
		$stats['active_plugins']   = $active_count;
		$stats['inactive_plugins'] = $inactive_count;

		// Check for excessive inactive plugins.
		$total_points += 25;
		$inactive_ratio = $total_plugins > 0 ? ( $inactive_count / $total_plugins ) : 0;
		$stats['inactive_ratio'] = round( $inactive_ratio * 100, 2 );

		if ( $inactive_ratio < 0.2 ) {
			// Less than 20% inactive is good.
			$earned_points += 25;
		} elseif ( $inactive_ratio < 0.4 ) {
			// 20-40% inactive is acceptable.
			$earned_points += 15;
			$warnings[] = sprintf(
				/* translators: %d: number of inactive plugins */
				__( '%d inactive plugins detected (consider removing unused plugins)', 'wpshadow' ),
				$inactive_count
			);
		} else {
			// Over 40% inactive is concerning.
			$warnings[] = sprintf(
				/* translators: %d: number of inactive plugins */
				__( 'High number of inactive plugins: %d (significant security risk)', 'wpshadow' ),
				$inactive_count
			);
		}

		// Check for outdated plugins.
		$total_points += 20;
		$update_plugins = get_site_transient( 'update_plugins' );
		$outdated_count = 0;

		if ( $update_plugins && isset( $update_plugins->response ) ) {
			$outdated_count = count( $update_plugins->response );
		}

		$stats['outdated_plugins'] = $outdated_count;

		if ( $outdated_count === 0 ) {
			$earned_points += 20;
		} elseif ( $outdated_count <= 2 ) {
			$earned_points += 15;
			$warnings[] = sprintf(
				/* translators: %d: number of outdated plugins */
				__( '%d plugin(s) need updates', 'wpshadow' ),
				$outdated_count
			);
		} else {
			$issues[] = sprintf(
				/* translators: %d: number of outdated plugins */
				__( '%d plugins are outdated (security risk)', 'wpshadow' ),
				$outdated_count
			);
		}

		// Check for plugin management tools.
		$total_points += 20;
		$management_plugins = array(
			'easy-updates-manager/easy-updates-manager.php' => 'Easy Updates Manager',
			'companion-auto-update/companion-auto-update.php' => 'Companion Auto Update',
			'plugin-inspector/plugin-inspector.php' => 'Plugin Inspector',
			'plugin-detective/plugin-detective.php' => 'Plugin Detective',
		);

		$active_management = array();
		foreach ( $management_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_management[] = $name;
			}
		}

		if ( ! empty( $active_management ) ) {
			$earned_points += 20;
		}

		$stats['management_tools'] = array(
			'found' => count( $active_management ),
			'list'  => $active_management,
		);

		// Check for reasonable total plugin count.
		$total_points += 15;

		if ( $active_count <= 15 ) {
			// 15 or fewer active plugins is manageable.
			$earned_points += 15;
		} elseif ( $active_count <= 25 ) {
			// 16-25 active plugins is acceptable.
			$earned_points += 10;
		} elseif ( $active_count <= 40 ) {
			// 26-40 is getting high.
			$earned_points += 5;
			$warnings[] = sprintf(
				/* translators: %d: number of active plugins */
				__( 'High number of active plugins: %d (may impact performance)', 'wpshadow' ),
				$active_count
			);
		} else {
			// Over 40 is excessive.
			$issues[] = sprintf(
				/* translators: %d: number of active plugins */
				__( 'Excessive active plugins: %d (significant performance and security risk)', 'wpshadow' ),
				$active_count
			);
		}

		// Check for security/monitoring plugins.
		$total_points += 10;
		$security_plugins = array(
			'wordfence/wordfence.php',
			'ithemes-security-pro/ithemes-security-pro.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'sucuri-scanner/sucuri.php',
		);

		$has_security = false;
		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_security = true;
				break;
			}
		}

		if ( $has_security ) {
			$earned_points += 10;
			$stats['has_security_plugin'] = true;
		} else {
			$stats['has_security_plugin'] = false;
			$warnings[] = __( 'No security plugin detected for plugin vulnerability monitoring', 'wpshadow' );
		}

		// Check for backup capability (for safe plugin removal).
		$total_points += 10;
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'duplicator/duplicator.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
		);

		$has_backup = false;
		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_backup = true;
				break;
			}
		}

		if ( $has_backup ) {
			$earned_points += 10;
			$stats['has_backup_plugin'] = true;
		} else {
			$stats['has_backup_plugin'] = false;
			$warnings[] = __( 'No backup plugin detected (recommended before removing plugins)', 'wpshadow' );
		}

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'medium';
		$threat_level = 45;

		if ( $score < 40 ) {
			$severity     = 'high';
			$threat_level = 55;
		} elseif ( $score >= 40 && $score < 70 ) {
			$severity     = 'medium';
			$threat_level = 40;
		} else {
			$severity     = 'low';
			$threat_level = 25;
		}

		// Return finding if plugin management is insufficient.
		if ( $score < 70 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: plugin management score percentage */
					__( 'Plugin management score: %d%%. Strategic plugin management reduces security risks and improves performance.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-management',
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
