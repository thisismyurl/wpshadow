<?php
/**
 * Webhook Validation Not Implemented Diagnostic
 *
 * Checks webhook validation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Webhook_Validation_Not_Implemented Class
 *
 * Performs diagnostic check for Webhook Validation Not Implemented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Webhook_Validation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webhook-validation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Webhook Validation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks webhook validation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'validate_webhooks' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Webhook validation not implemented. Always verify webhook signatures using HMAC to prevent spoofed requests.',
						'severity'   =>   'high',
						'threat_level'   =>   70,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/webhook-validation-not-implemented'
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
