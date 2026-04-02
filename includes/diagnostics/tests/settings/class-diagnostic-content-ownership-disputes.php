<?php
/**
 * Content Ownership Disputes Diagnostic
 *
 * Checks for missing content ownership policies and role controls.
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
 * Content Ownership Disputes Diagnostic
 *
 * Ensures content ownership policies are defined.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Content_Ownership_Disputes extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-ownership-disputes';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Ownership Disputes';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for missing content ownership policies and role controls';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		$policy_url = get_option( 'wpshadow_content_ownership_policy_url', '' );
		if ( empty( $policy_url ) ) {
			$issues[] = __( 'Content ownership policy URL not configured', 'wpshadow' );
		} else {
			$details['policy_url'] = esc_url_raw( $policy_url );
		}

		$editor_role = get_role( 'editor' );
		if ( $editor_role && $editor_role->has_cap( 'delete_others_posts' ) ) {
			$details['editors_can_delete_others'] = true;
		} else {
			$issues[] = __( 'Editor role cannot delete others posts - may cause ownership disputes', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Content ownership governance is not fully defined', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-ownership-disputes',
				'details'      => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
