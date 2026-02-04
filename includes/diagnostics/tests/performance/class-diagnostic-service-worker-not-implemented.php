<?php
/**
 * Service Worker Not Implemented Diagnostic
 *
 * Checks service worker implementation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Service_Worker_Not_Implemented Class
 *
 * Performs diagnostic check for Service Worker Not Implemented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Service_Worker_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'service-worker-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Service Worker Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks service worker implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for PWA/service worker plugins.
		$pwa_plugins = array(
			'pwa/pwa.php'                                => 'PWA',
			'super-progressive-web-apps/superpwa.php'    => 'Super Progressive Web Apps',
			'progressive-wp/progressivewebapp.php'       => 'Progressive Web App',
		);

		$pwa_detected = false;
		$pwa_plugin   = '';

		foreach ( $pwa_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$pwa_detected = true;
				$pwa_plugin   = $name;
				break;
			}
		}

		// Check for service-worker.js file.
		$sw_file_exists = file_exists( ABSPATH . 'service-worker.js' ) || file_exists( ABSPATH . 'sw.js' );

		// Check for service worker registration in HTML.
		$has_sw_script = has_action( 'wp_head' ) || has_action( 'wp_footer' );

		// If no PWA plugin and no service worker file.
		if ( ! $pwa_detected && ! $sw_file_exists ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Service worker not implemented. Service workers enable offline functionality, faster repeat visits, and Progressive Web App (PWA) features. Consider implementing if your site benefits from offline access.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/service-workers',
				'details'     => array(
					'pwa_detected'    => false,
					'sw_file_exists'  => false,
					'recommendation'  => __( 'Service workers are optional but beneficial for content-heavy sites. Install Super Progressive Web Apps plugin for easy PWA implementation.', 'wpshadow' ),
					'benefits'        => array(
						'offline_access' => 'Users can view cached content offline',
						'faster_loads'   => 'Cached assets load instantly',
						'pwa_features'   => 'Install to home screen, app-like experience',
					),
					'use_cases'       => array(
						'news_sites' => 'Offline reading',
						'ecommerce'  => 'Browse catalog offline',
						'blogs'      => 'Cache articles for offline',
					),
				),
			);
		}

		// No issues - service worker implemented or not needed.
		return null;
	}
}
