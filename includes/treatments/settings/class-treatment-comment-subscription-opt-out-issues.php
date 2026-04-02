<?php
/**
 * Comment Subscription Opt-Out Issues Treatment
 *
 * Checks whether comment subscription emails include opt-out options.
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
 * Comment Subscription Opt-Out Issues Treatment Class
 *
 * Detects missing opt-out settings for comment subscriptions.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Subscription_Opt_Out_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-subscription-opt-out-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Subscription Opt-Out Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment subscriptions provide opt-out links';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Subscription_Opt_Out_Issues' );
	}
}
