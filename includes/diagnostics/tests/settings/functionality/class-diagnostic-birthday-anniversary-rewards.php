<?php
/**
 * Birthday & Anniversary Rewards Diagnostic
 *
 * Checks if customer birthdays and purchase anniversaries are tracked
 * and celebrated to build emotional loyalty.
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
 * Birthday & Anniversary Rewards Diagnostic Class
 *
 * Emotional connection through birthday/anniversary rewards drives loyalty
 * and repeat purchases. Birthday emails get 5x higher engagement.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Birthday_Anniversary_Rewards extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'birthday-anniversary-rewards';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Birthday & Anniversary Rewards';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer birthdays and anniversaries are celebrated with special offers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'retention-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
		$has_edd         = is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' );

		if ( ! $has_woocommerce && ! $has_edd ) {
			return null; // Not applicable for non-ecommerce sites.
		}

		$has_birthday_field      = self::check_birthday_field();
		$has_anniversary_tracking = self::check_anniversary_tracking();
		$has_celebration_emails   = self::check_celebration_emails();
		$has_special_offers       = self::check_special_offers();

		// If all features present, no issue.
		if ( $has_birthday_field && $has_anniversary_tracking && $has_celebration_emails && $has_special_offers ) {
			return null;
		}

		$missing = array();
		if ( ! $has_birthday_field ) {
			$missing[] = 'birthday collection';
		}
		if ( ! $has_anniversary_tracking ) {
			$missing[] = 'anniversary tracking';
		}
		if ( ! $has_celebration_emails ) {
			$missing[] = 'celebration emails';
		}
		if ( ! $has_special_offers ) {
			$missing[] = 'special offers';
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: missing features */
				__( 'Birthday and anniversary rewards build emotional loyalty. Missing: %s. Birthday emails get 5x higher engagement than regular emails.', 'wpshadow' ),
				implode( ', ', $missing )
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/birthday-anniversary-rewards',
			'meta'         => array(
				'missing_features'         => $missing,
				'has_birthday_field'       => $has_birthday_field,
				'has_anniversary_tracking' => $has_anniversary_tracking,
				'has_celebration_emails'   => $has_celebration_emails,
				'has_special_offers'       => $has_special_offers,
			),
		);
	}

	/**
	 * Check if birthday field is collected.
	 *
	 * @since 1.6093.1200
	 * @return bool True if birthday field exists.
	 */
	private static function check_birthday_field(): bool {
		global $wpdb;

		// Check user meta for birthday field.
		$birthday_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE '%birthday%' OR meta_key LIKE '%birth_date%' LIMIT 1"
		);

		if ( $birthday_meta > 0 ) {
			return true;
		}

		// Check WooCommerce custom fields.
		$wc_birthday_field = get_option( 'woocommerce_checkout_birthday_field', '' );
		if ( ! empty( $wc_birthday_field ) ) {
			return true;
		}

		// Check for birthday-related plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$birthday_plugins = array(
			'woocommerce-birthday-discount/woocommerce-birthday-discount.php',
			'birthday-emails/birthday-emails.php',
		);

		foreach ( $birthday_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if purchase anniversary is tracked.
	 *
	 * @since 1.6093.1200
	 * @return bool True if anniversary tracking exists.
	 */
	private static function check_anniversary_tracking(): bool {
		// Check for first purchase date tracking.
		$anniversary_option = get_option( 'wpshadow_anniversary_tracking_enabled', false );
		if ( $anniversary_option ) {
			return true;
		}

		// Check for anniversary-related automations.
		$automations = get_option( 'wpshadow_email_automations', array() );
		if ( is_array( $automations ) ) {
			foreach ( $automations as $automation ) {
				$trigger = $automation['trigger'] ?? '';
				if ( false !== strpos( $trigger, 'anniversary' ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if celebration emails are configured.
	 *
	 * @since 1.6093.1200
	 * @return bool True if celebration emails exist.
	 */
	private static function check_celebration_emails(): bool {
		// Check for scheduled birthday/anniversary emails.
		$cron_hooks = array(
			'wpshadow_send_birthday_emails',
			'wpshadow_send_anniversary_emails',
			'woocommerce_birthday_email',
		);

		foreach ( $cron_hooks as $hook ) {
			if ( wp_next_scheduled( $hook ) ) {
				return true;
			}
		}

		// Check email templates.
		$email_templates = array(
			'wpshadow_birthday_email_template',
			'wpshadow_anniversary_email_template',
		);

		foreach ( $email_templates as $template ) {
			$content = get_option( $template, '' );
			if ( ! empty( $content ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if special birthday offers are configured.
	 *
	 * @since 1.6093.1200
	 * @return bool True if special offers exist.
	 */
	private static function check_special_offers(): bool {
		global $wpdb;

		// Check for birthday coupons.
		if ( function_exists( 'WC' ) ) {
			$coupons = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} 
					WHERE post_type = %s 
					AND (post_title LIKE %s OR post_title LIKE %s OR post_excerpt LIKE %s)
					LIMIT 1",
					'shop_coupon',
					'%birthday%',
					'%anniversary%',
					'%birthday%'
				)
			);

			if ( $coupons > 0 ) {
				return true;
			}
		}

		// Check for birthday discount settings.
		$birthday_discount = get_option( 'wpshadow_birthday_discount_enabled', false );
		if ( $birthday_discount ) {
			return true;
		}

		return false;
	}
}
