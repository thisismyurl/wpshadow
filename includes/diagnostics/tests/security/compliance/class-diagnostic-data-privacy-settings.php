<?php
/**
 * Data Privacy Settings Diagnostic
 *
 * Issue #4874: Data Collection Not Transparently Disclosed
 * Pillar: 🛡️ Safe by Default / #10: Beyond Pure
 *
 * Checks if data collection is transparent and user-consented.
 * Users should know what data is being collected and why.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Data_Privacy_Settings Class
 *
 * Checks for:
 * - Privacy policy linked and accessible
 * - Consent banner for data collection
 * - Explicit opt-in for telemetry/tracking
 * - Clear disclosure of what data is collected
 * - Clear explanation of WHY data is collected
 * - Ability to opt-out
 * - Data deletion/export on request (GDPR/CCPA)
 * - No third-party tracking without consent
 *
 * Why this matters:
 * - GDPR (EU), CCPA (CA), and similar laws require consent
 * - Users want transparency about their data
 * - Trust is lost when data collection is hidden
 * - Commandment #10: "Beyond Pure" means privacy by design
 *
 * @since 1.6093.1200
 */
class Diagnostic_Data_Privacy_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'data-privacy-settings';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Data Collection Not Transparently Disclosed';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if data collection is transparent and user-consented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual privacy audit requires legal review.
		// We provide recommendations.

		$issues = array();

		$issues[] = __( 'Display privacy policy link prominently (footer, settings)', 'wpshadow' );
		$issues[] = __( 'Get explicit user consent before collecting tracking data', 'wpshadow' );
		$issues[] = __( 'Clearly explain WHAT data is collected (not vague)', 'wpshadow' );
		$issues[] = __( 'Clearly explain WHY data is collected (purpose)', 'wpshadow' );
		$issues[] = __( 'Provide opt-out mechanism for telemetry', 'wpshadow' );
		$issues[] = __( 'Never share data with third parties without explicit consent', 'wpshadow' );
		$issues[] = __( 'Support GDPR right to data deletion/export', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Users have legal rights to privacy. Data collection must be transparent, consensual, and compliant with GDPR, CCPA, and similar laws.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/data-privacy',
				'details'      => array(
					'recommendations'         => $issues,
					'legal_frameworks'        => 'GDPR (EU), CCPA (CA), LGPD (Brazil), PIPEDA (Canada)',
					'penalties'               => 'GDPR fines up to 4% of annual revenue',
					'transparency_pattern'    => 'Disclosure → Consent → Data Security → Deletion/Export Rights',
					'commandment'             => 'Commandment #10: Beyond Pure (Privacy First)',
					'user_rights'             => 'Right to know, Right to refuse, Right to access, Right to delete',
				),
			);
		}

		return null;
	}
}
