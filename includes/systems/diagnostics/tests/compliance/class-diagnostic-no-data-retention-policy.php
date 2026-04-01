<?php
/**
 * No Data Retention Policy Diagnostic
 *
 * Detects when data retention policies are not documented,
 * violating GDPR and creating legal risk.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Compliance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Data Retention Policy
 *
 * Checks whether data retention policies are
 * documented and implemented.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Data_Retention_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-data-retention-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Retention Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether data retention policy is documented';

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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for documented retention policy
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );

		if ( $privacy_page_id > 0 ) {
			$privacy_page = get_post( $privacy_page_id );
			if ( $privacy_page ) {
				$content = $privacy_page->post_content;
				$has_retention_policy = preg_match( '/(?:data\s+retention|how\s+long\s+we\s+(?:keep|store|retain)|retention\s+period)/i', $content );

				if ( $has_retention_policy ) {
					return null;
				}
			}
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => __(
				'You don\'t have a documented data retention policy, which violates GDPR\'s principle of "storage limitation." GDPR requires: explaining how long you keep data ("30 days", "5 years"), justifying why that duration is necessary, deleting data after retention period. Without a policy, you\'re keeping data indefinitely (compliance violation) and exposing yourself to breaches. Document retention periods in your privacy policy and implement automated cleanup.',
				'wpshadow'
			),
			'severity'      => 'high',
			'threat_level'  => 70,
			'auto_fixable'  => false,
			'business_impact' => array(
				'metric'         => 'GDPR Compliance & Data Risk',
				'potential_gain' => 'Avoid GDPR violations, reduce data breach exposure',
				'roi_explanation' => 'Data retention policies are GDPR-required. Missing them creates violation risk and increases breach exposure.',
			),
			'kb_link'       => 'https://wpshadow.com/kb/data-retention-policy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
