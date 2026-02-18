<?php
/**
 * Security Updates Applied Diagnostic
 *
 * Tests if WordPress and plugins are kept up to date.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Updates Applied Diagnostic Class
 *
 * Checks for pending core/plugin/theme updates.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Applies_Security_Updates extends Diagnostic_Base {

	protected static $slug = 'applies-security-updates';
	protected static $title = 'Security Updates Applied';
	protected static $description = 'Tests if WordPress and plugins are kept up to date';
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugin_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		$core_updates = get_core_updates();
		$plugin_updates = get_plugin_updates();
		$theme_updates = get_theme_updates();

		$core_pending = ! empty( $core_updates ) && isset( $core_updates[0]->response ) && 'upgrade' === $core_updates[0]->response;
		$plugin_pending = ! empty( $plugin_updates );
		$theme_pending = ! empty( $theme_updates );

		if ( ! $core_pending && ! $plugin_pending && ! $theme_pending ) {
			return null;
		}

		$parts = array();
		if ( $core_pending ) {
			$parts[] = __( 'WordPress core update available', 'wpshadow' );
		}
		if ( $plugin_pending ) {
			$parts[] = __( 'plugin updates available', 'wpshadow' );
		}
		if ( $theme_pending ) {
			$parts[] = __( 'theme updates available', 'wpshadow' );
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: update types */
				__( 'Updates pending: %s. Apply security updates to reduce vulnerability risk.', 'wpshadow' ),
				implode( ', ', $parts )
			),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/security-updates-applied',
			'persona'      => 'developer',
		);
	}
}
