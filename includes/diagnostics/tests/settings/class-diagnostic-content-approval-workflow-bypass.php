<?php
/**
 * Content Approval Workflow Bypass Diagnostic
 *
 * Checks for role permissions that bypass editorial workflows.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Approval Workflow Bypass Diagnostic
 *
 * Validates publish capabilities for non-admin roles.
 *
 * @since 1.6030.2240
 */
class Diagnostic_Content_Approval_Workflow_Bypass extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-approval-workflow-bypass';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Approval Workflow Bypass';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for role permissions that bypass editorial workflows';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		$contributor = get_role( 'contributor' );
		if ( $contributor && $contributor->has_cap( 'publish_posts' ) ) {
			$issues[] = __( 'Contributors can publish posts without approval', 'wpshadow' );
		}

		$author = get_role( 'author' );
		if ( $author && $author->has_cap( 'publish_posts' ) ) {
			$details['authors_can_publish'] = true;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Editorial workflow can be bypassed by role permissions', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-approval-workflow-bypass',
				'details'      => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
