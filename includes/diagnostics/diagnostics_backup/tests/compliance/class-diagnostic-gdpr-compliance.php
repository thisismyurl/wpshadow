<?php
/**
 * Diagnostic: GDPR Compliance Check
 *
 * Detects GDPR compliance issues related to data handling and privacy.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_GDPR_Compliance
 *
 * Checks for basic GDPR compliance requirements including privacy policy,
 * cookie consent, and user data management capabilities.
 *
 * @since 1.2601.2148
 */
class Diagnostic_GDPR_Compliance extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-compliance';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Compliance Check';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect GDPR compliance issues related to data handling and privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies basic GDPR compliance elements are in place.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if non-compliant, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Privacy policy page
		$privacy_policy_page_id = get_option( 'wp_page_for_privacy_policy' );
		
		if ( empty( $privacy_policy_page_id ) || 'publish' !== get_post_status( $privacy_policy_page_id ) ) {
			$issues[] = __( 'No privacy policy page configured or published.', 'wpshadow' );
		}

		// Check 2: Cookie consent mechanism
		$cookie_consent_plugins = array(
			'cookie-law-info/cookie-law-info.php' => 'Cookie Law Info',
			'gdpr-cookie-consent/gdpr-cookie-consent.php' => 'GDPR Cookie Consent',
			'cookiebot/cookiebot.php' => 'Cookiebot',
			'complianz-gdpr/complianz-gpdr.php' => 'Complianz',
			'cookie-notice/cookie-notice.php' => 'Cookie Notice',
		);

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_cookie_consent = false;
		foreach ( $cookie_consent_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_cookie_consent = true;
				break;
			}
		}

		if ( ! $has_cookie_consent ) {
			$issues[] = __( 'No cookie consent plugin detected. GDPR requires user consent for non-essential cookies.', 'wpshadow' );
		}

		// Check 3: User data export capability (WordPress 4.9.6+)
		if ( ! function_exists( 'wp_privacy_exports_dir' ) ) {
			$issues[] = __( 'WordPress version does not support GDPR user data export features. Consider updating WordPress.', 'wpshadow' );
		}

		// Check 4: Check for common tracking scripts without consent
		$tracking_scripts = array(
			'google-analytics',
			'gtag',
			'facebook pixel',
			'hotjar',
			'mouseflow',
		);

		$has_tracking = false;
		$home_content = '';
		
		// Fetch homepage HTML
		$homepage_id = get_option( 'page_on_front' );
		if ( $homepage_id ) {
			$homepage = get_post( $homepage_id );
			if ( $homepage ) {
				$home_content = strtolower( $homepage->post_content );
			}
		}

		// Check header scripts
		ob_start();
		wp_head();
		$header_content = strtolower( ob_get_clean() );

		foreach ( $tracking_scripts as $script ) {
			if ( false !== strpos( $header_content, $script ) || false !== strpos( $home_content, $script ) ) {
				$has_tracking = true;
				break;
			}
		}

		if ( $has_tracking && ! $has_cookie_consent ) {
			$issues[] = __( 'Tracking scripts detected but no cookie consent mechanism found. This may violate GDPR.', 'wpshadow' );
		}

		
		// Check 6: Feature initialization
		if ( ! (get_option( "features_init" ) !== false) ) {
			$issues[] = __( 'Feature initialization', 'wpshadow' );
		}

		// Check 7: Database tables
		if ( ! (! empty( $GLOBALS["wpdb"] )) ) {
			$issues[] = __( 'Database tables', 'wpshadow' );
		}

		// Check 8: Hook registration
		if ( ! (has_action( "init" )) ) {
			$issues[] = __( 'Hook registration', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			// Basic GDPR compliance appears in place
			return null;
		}

		$severity = count( $issues ) > 2 ? 'high' : 'medium';
		$threat_level = count( $issues ) > 2 ? 70 : 40;

		$description = sprintf(
			/* translators: %d: number of compliance issues */
			_n(
				'Found %d GDPR compliance issue. GDPR violations can result in fines up to €20 million or 4%% of annual global turnover.',
				'Found %d GDPR compliance issues. GDPR violations can result in fines up to €20 million or 4%% of annual global turnover.',
				count( $issues ),
				'wpshadow'
			),
			count( $issues )
		) . ' ' . implode( ' ', $issues );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/compliance-gdpr-compliance',
			'meta'        => array(
				'issues' => $issues,
				'issue_count' => count( $issues ),
				'privacy_policy_configured' => ! empty( $privacy_policy_page_id ),
				'cookie_consent_active' => $has_cookie_consent,
			),
		);
	}
}
