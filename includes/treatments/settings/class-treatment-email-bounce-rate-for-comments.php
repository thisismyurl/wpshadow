<?php
/**
 * Email Bounce Rate for Comments Treatment
 *
 * Checks whether comment notification emails have bounce handling.
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
 * Email Bounce Rate for Comments Treatment Class
 *
 * Detects missing bounce handling for comment notification emails.
 *
 * @since 1.6093.1200
 */
class Treatment_Email_Bounce_Rate_For_Comments extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-bounce-rate-for-comments';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Email Bounce Rate for Comments';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if bounce handling is configured for comment emails';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Email_Bounce_Rate_For_Comments' );
	}
}
