<?php
/**
 * Email Bounce Rate for Comments Diagnostic
 *
 * Checks whether comment notification emails have bounce handling.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Bounce Rate for Comments Diagnostic Class
 *
 * Detects missing bounce handling for comment notification emails.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Email_Bounce_Rate_For_Comments extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-bounce-rate-for-comments';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Bounce Rate for Comments';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if bounce handling is configured for comment emails';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! get_option( 'comments_notify' ) ) {
			return null;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'post-smtp/postman-smtp.php',
			'easy-wp-smtp/easy-wp-smtp.php',
		);

		$has_smtp = false;
		foreach ( $smtp_plugins as $plugin_file ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				$has_smtp = true;
				break;
			}
		}

		$has_bounce_handler = (bool) has_action( 'wp_mail_failed' );

		if ( ! $has_smtp && ! $has_bounce_handler ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment notification emails may be bouncing without proper handling. Consider adding SMTP or bounce monitoring.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'smtp_plugin_active' => $has_smtp,
					'bounce_handler'     => $has_bounce_handler,
				),
				'kb_link'      => 'https://wpshadow.com/kb/email-bounce-rate-for-comments?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
