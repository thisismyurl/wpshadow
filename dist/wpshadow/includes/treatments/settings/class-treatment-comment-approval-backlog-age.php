<?php
/**
 * Comment Approval Backlog Age Treatment
 *
 * Checks how long comments have been waiting for approval.
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
 * Comment Approval Backlog Age Treatment Class
 *
 * Detects stale pending comments.
 *
 * @since 0.6093.1200
 */
class Treatment_Comment_Approval_Backlog_Age extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-approval-backlog-age';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Approval Backlog Age';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks how long pending comments have waited for approval';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Approval_Backlog_Age' );
	}
}
