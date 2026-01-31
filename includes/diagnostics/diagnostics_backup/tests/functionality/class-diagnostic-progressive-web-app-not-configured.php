<?php
/**
 * Progressive Web App Not Configured Diagnostic
 *
 * Checks if PWA is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Progressive Web App Not Configured Diagnostic Class
 *
 * Detects missing PWA configuration.
 *
 * @since 1.2601.2340
 */
class Diagnostic_Progressive_Web_App_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'progressive-web-app-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Progressive Web App Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PWA is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for PWA plugins
		$pwa_plugins = array(
			'pwa/pwa.php',
			'super-progressive-web-app/super-progressive-web-app.php',
		);

		$pwa_active = false;
		foreach ( $pwa_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$pwa_active = true;
				break;
			}
		}

		if ( ! $pwa_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Progressive Web App (PWA) is not configured. Enable PWA support for offline access and app-like experience.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/progressive-web-app-not-configured',
			);
		}

		return null;
	}
}
