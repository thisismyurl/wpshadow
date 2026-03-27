<?php
/**
 * Media Workflow Approval Process Treatment
 *
 * Tests editorial workflow for media approvals.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Workflow Approval Process Treatment Class
 *
 * Verifies editorial workflow for media approvals,
 * including pending/approved status tracking and permissions.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Workflow_Approval_Process extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-workflow-approval-process';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Workflow Approval Process';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests editorial workflow for media approvals';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Workflow_Approval_Process' );
	}
}
