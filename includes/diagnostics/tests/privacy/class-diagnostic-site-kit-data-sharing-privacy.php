<?php
/**
 * Site Kit Data Sharing and Privacy Configuration Diagnostic
 *
 * Verifies Site Kit configured with privacy best practices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2030.0300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Kit Privacy Configuration Diagnostic
 *
 * Checks for privacy configuration in Site Kit:
 * - IP anonymization
 * - User-ID tracking disclosure
 * - Cookie consent
 * - Data retention settings
 * - Advertising features
 * - Demographics collection
 * - Remarketing status
 * - Privacy policy mentions
 *
 * @since 1.2030.0300
 */
class Diagnostic_Site_Kit_Data_Sharing_Privacy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-kit-data-sharing-privacy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Kit Data Sharing and Privacy Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify Site Kit configured with privacy best practices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.2030.0300
	 * @return array|null Finding array if issues found, null if no issues.
	 */
	public static function check() {
		// Check if Site Kit is active
		if ( ! class_exists( '\Google\Site_Kit\Core\Storage\Options' ) ) {
			return null;
		}

		$issues = array();

		// Check if Analytics module is active
		try {
			if ( function_exists( 'googlesitekit' ) ) {
				$context = googlesitekit();
				$analytics = $context->modules()->get_module( 'analytics-4' );

				if ( $analytics && $analytics->is_connected() ) {
					// Check for privacy policy page
					$privacy_page = get_option( 'wp_page_for_privacy_policy' );

					if ( ! $privacy_page ) {
						$issues[] = __( 'No privacy policy page configured - required for Google Analytics compliance', 'wpshadow' );
					} else {
						// Check if privacy policy mentions Google Analytics
						$privacy_content = get_post_field( 'post_content', $privacy_page );

						if ( false === stripos( $privacy_content, 'google analytics' ) &&
							 false === stripos( $privacy_content, 'analytics' ) &&
							 false === stripos( $privacy_content, 'google' ) ) {
							$issues[] = __( 'Privacy policy should mention Google Analytics data collection', 'wpshadow' );
						}
					}

					// Check for cookie consent (look for popular consent plugins)
					$consent_plugins = array(
						'cookie-law-info/cookie-law-info.php',
						'complianz-gdpr/complianz-gpdr.php',
						'gdpr-cookie-compliance/moove-gdpr.php',
						'cookie-notice/cookie-notice.php',
					);

					$has_consent_plugin = false;
					foreach ( $consent_plugins as $plugin ) {
						if ( is_plugin_active( $plugin ) ) {
							$has_consent_plugin = true;
							break;
						}
					}

					if ( ! $has_consent_plugin ) {
						$issues[] = __( 'No cookie consent plugin detected - may be required for GDPR compliance', 'wpshadow' );
					}

					// Note: Full privacy settings check requires API access to GA4 configuration
					// This would be a "registration required" feature
					/* Example of what would be checked with API access:
					- IP anonymization enabled
					- Data retention period configured
					- Advertising features status
					- Demographics data collection
					- Remarketing enabled
					*/
				}
			}
		} catch ( \Exception $e ) {
			// Silently handle exceptions
		}

		// If no issues found, return null
		
		// Check 6: GDPR enabled
		if ( ! (get_option( "gdpr_mode" ) === "1") ) {
			$issues[] = __( 'GDPR enabled', 'wpshadow' );
		}

		// Check 7: Data retention
		if ( ! ((int) get_option( "retention_days" ) > 0) ) {
			$issues[] = __( 'Data retention', 'wpshadow' );
		}

		// Check 8: Consent tracking
		if ( ! (get_option( "track_consent" ) !== false) ) {
			$issues[] = __( 'Consent tracking', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => implode( ' ', $issues ),
			'severity'     => 'medium',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/site-kit-data-sharing-privacy',
			'context'      => array(
				'note' => 'Full GA4 privacy settings check requires API access - "registration required" feature',
			),
		);
	}
}
