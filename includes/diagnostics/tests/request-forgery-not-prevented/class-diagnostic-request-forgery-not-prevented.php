<?php
/**
 * Request Forgery Not Prevented Diagnostic
 *
 * Checks request forgery.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Request_Forgery_Not_Prevented Class
 *
 * Performs diagnostic check for Request Forgery Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Request_Forgery_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'request-forgery-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Request Forgery Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks request forgery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'validate_request_origin' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Request forgery not prevented. Implement CSRF tokens and validate request origins for state-changing operations.',
						'severity'   =>   'high',
						'threat_level'   =>   75,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/request-forgery-not-prevented'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
