<?php
/**
 * Comment Subscription Opt-Out Issues Treatment
 *
 * Checks whether comment subscription emails include opt-out options.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1331
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
 * @since 1.5049.1331
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
	 * @since  1.5049.1331
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
