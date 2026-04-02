<?php
/**
 * OWASP Top 10 Compliance Not Verified Diagnostic
 *
 * Checks OWASP Top 10 compliance.
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
 * Diagnostic_OWASP_Top_10_Compliance_Not_Verified Class
 *
 * Performs diagnostic check for Owasp Top 10 Compliance Not Verified.
 *
 * @since 1.6093.1200
 */
class Diagnostic_OWASP_Top_10_Compliance_Not_Verified extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'owasp-top-10-compliance-not-verified';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'OWASP Top 10 Compliance Not Verified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks OWASP Top 10 compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'owasp_compliance_checked' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'OWASP Top 10 coverage has not been verified yet. A regular review against common web risks helps keep your site security posture strong.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/owasp-top-10-compliance-not-verified',
			);
		}

		return null;
	}
}
