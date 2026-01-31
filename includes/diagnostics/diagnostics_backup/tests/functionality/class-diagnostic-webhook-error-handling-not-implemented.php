<?php
/**
 * Webhook Error Handling Not Implemented Diagnostic
 *
 * Checks if webhook error handling is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook Error Handling Not Implemented Diagnostic Class
 *
 * Detects missing webhook error handling.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Webhook_Error_Handling_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webhook-error-handling-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Webhook Error Handling Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if webhook error handling is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_filter;

		// Check if webhook hooks are registered but no error handling
		if ( isset( $wp_filter['rest_insert_' ] ) || has_filter( 'rest_api_init' ) ) {
			// Webhooks are used - check if error handling is in place
			if ( ! has_filter( 'wp_rest_server_class' ) ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Webhooks are active but no error handling is configured. Webhook failures may not be logged or retried.', 'wpshadow' ),
					'severity'      => 'medium',
					'threat_level'  => 35,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/webhook-error-handling-not-implemented',
				);
			}
		}

		return null;
	}
}
