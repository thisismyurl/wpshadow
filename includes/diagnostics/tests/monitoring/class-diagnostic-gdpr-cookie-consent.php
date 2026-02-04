<?php
/**
 * GDPR Cookie Consent Diagnostic
 *
 * Analyzes GDPR cookie consent implementation and compliance.
 *
 * @since   1.6033.2135
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR Cookie Consent Diagnostic
 *
 * Evaluates GDPR cookie consent banner implementation and compliance.
 *
 * @since 1.6033.2135
 */
class Diagnostic_GDPR_Cookie_Consent extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-cookie-consent';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Cookie Consent';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes GDPR cookie consent implementation and compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2135
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for GDPR cookie consent plugins
		$consent_plugins = array(
			'cookie-law-info/cookie-law-info.php'       => 'Cookie Law Info',
			'gdpr-cookie-consent/gdpr-cookie-consent.php' => 'GDPR Cookie Consent',
			'complianz-gdpr/complianz-gdpr.php'         => 'Complianz',
			'cookie-notice/cookie-notice.php'           => 'Cookie Notice',
			'cookiebot/cookiebot.php'                   => 'Cookiebot',
		);

		$active_plugin = null;
		foreach ( $consent_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugin = $name;
				break;
			}
		}

		// Check if site uses cookies (common indicators)
		global $wp_scripts;
		$uses_analytics = false;
		$uses_advertising = false;

		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! isset( $script->src ) ) {
					continue;
				}

				// Check for analytics scripts
				if ( strpos( $script->src, 'google-analytics' ) !== false ||
				     strpos( $script->src, 'googletagmanager' ) !== false ||
				     strpos( $script->src, 'analytics.js' ) !== false ) {
					$uses_analytics = true;
				}

				// Check for advertising scripts
				if ( strpos( $script->src, 'googlesyndication' ) !== false ||
				     strpos( $script->src, 'doubleclick' ) !== false ||
				     strpos( $script->src, 'facebook.net' ) !== false ) {
					$uses_advertising = true;
				}
			}
		}

		// Generate findings if cookies detected without consent mechanism
		if ( ( $uses_analytics || $uses_advertising ) && ! $active_plugin ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Analytics/advertising cookies detected without GDPR consent mechanism. EU law requires explicit consent before setting non-essential cookies.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-cookie-consent',
				'meta'         => array(
					'uses_analytics'    => $uses_analytics,
					'uses_advertising'  => $uses_advertising,
					'active_plugin'     => $active_plugin,
					'recommendation'    => 'Install GDPR cookie consent plugin (Complianz or Cookie Law Info)',
					'legal_requirement' => 'GDPR Article 7 requires explicit consent',
					'fines'             => 'Up to €20 million or 4% of annual revenue',
					'required_features' => array(
						'Cookie consent banner',
						'Granular consent options',
						'Consent logging',
						'Easy opt-out mechanism',
						'Cookie policy page',
					),
				),
			);
		}

		return null;
	}
}
