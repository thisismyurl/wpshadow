<?php
/**
 * Comment Subscription Opt-Out Issues Diagnostic
 *
 * Checks whether comment subscription emails include opt-out options.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Subscription Opt-Out Issues Diagnostic Class
 *
 * Detects missing opt-out settings for comment subscriptions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Comment_Subscription_Opt_Out_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-subscription-opt-out-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Subscription Opt-Out Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment subscriptions provide opt-out links';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$subscription_plugins = array(
			'subscribe-to-comments/subscribe-to-comments.php',
			'subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php',
		);

		$active_subscription = false;
		foreach ( $subscription_plugins as $plugin_file ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				$active_subscription = true;
				break;
			}
		}

		if ( ! $active_subscription ) {
			return null;
		}

		$stcr_options = get_option( 'stcr_options', array() );
		$unsubscribe_text = '';

		if ( is_array( $stcr_options ) ) {
			foreach ( $stcr_options as $key => $value ) {
				if ( false !== stripos( (string) $key, 'unsub' ) && ! empty( $value ) ) {
					$unsubscribe_text = (string) $value;
					break;
				}
			}
		}

		if ( '' === $unsubscribe_text ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment subscription emails may be missing clear opt-out instructions. Ensure unsubscribe text and links are configured.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'subscription_plugin_active' => $active_subscription,
				),
				'kb_link'      => 'https://wpshadow.com/kb/comment-subscription-opt-out-issues',
			);
		}

		return null;
	}
}
