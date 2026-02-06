<?php
/**
 * No GDPR Consent Management Diagnostic
 *
 * Detects when GDPR consent management is not implemented,
 * exposing the site to legal compliance risks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Compliance
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No GDPR Consent Management
 *
 * Checks whether GDPR consent management is implemented
 * for tracking and cookies.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_GDPR_Consent_Management extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-gdpr-consent-management';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Consent Management';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether GDPR consent management is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for consent management plugins
		$has_consent_management = is_plugin_active( 'cookiebot/cookiebot.php' ) ||
			is_plugin_active( 'gdpr-cookie-consent/gdpr-cookie-consent.php' ) ||
			is_plugin_active( 'wp-gdpr-compliance/wp-gdpr-compliance.php' ) ||
			is_plugin_active( 'iubenda-cookie-law-solution/iubenda.php' );

		if ( ! $has_consent_management ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your site doesn\'t have GDPR consent management, which is legally required if you collect data from EU visitors. GDPR violations carry fines up to €20 million or 4% of global annual revenue. You need: consent before tracking (cookies, analytics, pixels), clear privacy policy, easy opt-out. Even if you\'re not in EU, GDPR applies to any EU visitor data you collect. This is non-negotiable for legal compliance.',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 95,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Legal Compliance',
					'potential_gain' => 'Avoid GDPR violations (€20M fines)',
					'roi_explanation' => 'GDPR compliance is legally required for EU data. Violations carry €20M fines or 4% of global revenue. Absolutely critical.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/gdpr-consent-management',
			);
		}

		return null;
	}
}
