<?php
/**
 * No Progressive Web App Support Diagnostic
 *
 * Detects when PWA features are not implemented,
 * missing mobile app-like experience opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Progressive Web App Support
 *
 * Checks whether PWA features are enabled
 * for app-like mobile experience.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Progressive_Web_App_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-progressive-web-app-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Progressive Web App (PWA) Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether PWA features are enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for PWA manifest
		$manifest_url = home_url( '/manifest.json' );
		$manifest_check = wp_remote_get( $manifest_url );
		$has_manifest = ! is_wp_error( $manifest_check ) && wp_remote_retrieve_response_code( $manifest_check ) === 200;

		// Check for PWA plugins
		$has_pwa_plugin = is_plugin_active( 'pwa/pwa.php' ) ||
			is_plugin_active( 'super-progressive-web-apps/superpwa.php' );

		if ( ! $has_manifest && ! $has_pwa_plugin ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'PWA features aren\'t enabled, which means you\'re missing mobile app-like capabilities. PWAs provide: "Add to Home Screen" button (appears on user\'s phone like an app), offline access (content available without internet), push notifications, faster load times. PWAs increase engagement: 2-4x longer sessions, 70% increase in mobile conversions. Many PWA plugins add these features in minutes.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Mobile Engagement & Conversion',
					'potential_gain' => '+70% mobile conversion, 2-4x longer sessions',
					'roi_explanation' => 'PWAs provide app-like mobile experience, increasing mobile conversion by 70% and session length by 2-4x.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/progressive-web-app-support?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
