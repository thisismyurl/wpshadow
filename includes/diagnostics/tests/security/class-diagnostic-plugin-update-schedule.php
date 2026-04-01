<?php
/**
 * Plugin Update Schedule Diagnostic
 *
 * Issue #4907: Plugins Not Configured for Auto-Updates
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if plugin auto-updates are enabled.
 * Security patches must be applied quickly.
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
 * Diagnostic_Plugin_Update_Schedule Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Update_Schedule extends Diagnostic_Base {

	protected static $slug = 'plugin-update-schedule';
	protected static $title = 'Plugins Not Configured for Auto-Updates';
	protected static $description = 'Checks if security updates are applied automatically';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		// Check if auto-updates are enabled globally
		if ( ! defined( 'WP_AUTO_UPDATE_CORE' ) || WP_AUTO_UPDATE_CORE !== true ) {
			$issues[] = __( 'Enable automatic core updates for security releases', 'wpshadow' );
		}

		// Get all plugins
		$all_plugins = get_plugins();
		$auto_update_plugins = get_site_option( 'auto_update_plugins', array() );

		$auto_update_count = count( $auto_update_plugins );
		$total_plugins = count( $all_plugins );

		if ( $auto_update_count < $total_plugins ) {
			$issues[] = sprintf(
				/* translators: %1$d: plugins with auto-updates, %2$d: total plugins */
				__( 'Only %1$d of %2$d plugins have auto-updates enabled', 'wpshadow' ),
				$auto_update_count,
				$total_plugins
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Security vulnerabilities are exploited within hours of disclosure. Auto-updates ensure patches are applied immediately.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/auto-updates?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'auto_update_count'       => $auto_update_count,
					'total_plugins'           => $total_plugins,
					'exploit_timing'          => 'Vulnerabilities exploited within 6-24 hours',
				),
			);
		}

		return null;
	}
}
