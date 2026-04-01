<?php
/**
 * Team Member Onboarding Process Diagnostic
 *
 * Checks if onboarding documentation and access policies are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Team Member Onboarding Process Diagnostic
 *
 * Validates onboarding checklist configuration and admin access review.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Team_Member_Onboarding_Process extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'team-member-onboarding-process';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Team Member Onboarding Process';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if onboarding documentation and access policies are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		$onboarding_url = get_option( 'wpshadow_onboarding_docs_url', '' );
		$access_review = get_option( 'wpshadow_access_review_schedule', '' );

		if ( empty( $onboarding_url ) ) {
			$issues[] = __( 'Onboarding documentation URL not configured', 'wpshadow' );
		} else {
			$details['onboarding_url'] = esc_url_raw( $onboarding_url );
		}

		if ( empty( $access_review ) ) {
			$issues[] = __( 'Admin access review schedule not configured', 'wpshadow' );
		} else {
			$details['access_review'] = sanitize_text_field( $access_review );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Team onboarding process needs documentation or access review', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/team-member-onboarding-process?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
