<?php
/**
 * Comment Approval Workflow Treatment
 *
 * Verifies comment approval workflow is properly configured.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Approval Workflow Treatment Class
 *
 * Checks comment moderation workflow configuration.
 *
 * @since 0.6093.1200
 */
class Treatment_Comment_Approval_Workflow extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-approval-workflow';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Approval Workflow';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment moderation workflow';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Approval_Workflow' );
	}
}
