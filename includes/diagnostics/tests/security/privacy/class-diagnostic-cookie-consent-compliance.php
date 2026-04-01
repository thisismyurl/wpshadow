<?php
/**
 * Cookie Consent Compliance Diagnostic
 *
 * Checks whether cookie consent tools are in place.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Consent Compliance Diagnostic Class
 *
 * Verifies that cookie consent tools are active.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Cookie_Consent_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cookie-consent-compliance';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Cookie Consent Banner Missing or Non-Compliant';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cookie consent tools are configured';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$consent_plugins = array(
			'cookie-law-info/cookie-law-info.php' => 'Cookie Law Info',
			'gdpr-cookie-consent/gdpr-cookie-consent.php' => 'GDPR Cookie Consent',
			'complianz-gdpr/complianz-gdpr.php' => 'Complianz',
			'cookie-notice/cookie-notice.php' => 'Cookie Notice',
			'cookiebot/cookiebot.php' => 'Cookiebot',
		);

		$active_plugins = array();
		foreach ( $consent_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_plugins[] = $plugin_name;
			}
		}

		$stats['consent_tools'] = ! empty( $active_plugins ) ? implode( ', ', $active_plugins ) : 'none';

		if ( empty( $active_plugins ) ) {
			$issues[] = __( 'No cookie consent tool detected for non-essential cookies', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cookie consent helps visitors choose how their data is used. A clear opt-in banner builds trust and supports privacy laws.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cookie-consent-compliance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
