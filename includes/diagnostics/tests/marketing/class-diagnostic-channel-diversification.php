<?php
/**
 * Channel Diversification Diagnostic
 *
 * Checks whether acquisition channels are diversified.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Channel Diversification Diagnostic Class
 *
 * Verifies that multiple acquisition channels are active.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Channel_Diversification extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'channel-diversification';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Acquisition Channel Diversification';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether multiple acquisition channels are active';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'growth-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		$channels = 0;

		// SEO tools channel (30 points).
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'         => 'Yoast SEO',
			'rank-math/rank-math.php'           => 'Rank Math',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
		);

		if ( self::has_active_plugin( $seo_plugins ) ) {
			$channels++;
			$earned_points += 30;
			$stats['seo_channel'] = 'enabled';
		}

		// Email marketing channel (25 points).
		$email_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php' => 'Mailchimp for WP',
			'newsletter/newsletter.php'            => 'Newsletter',
			'fluentcrm/fluentcrm.php'              => 'FluentCRM',
		);

		if ( self::has_active_plugin( $email_plugins ) ) {
			$channels++;
			$earned_points += 25;
			$stats['email_channel'] = 'enabled';
		}

		// Paid ads channel (25 points).
		$paid_plugins = array(
			'google-tag-manager-for-wordpress/google-tag-manager-for-wordpress.php' => 'GTM4WP',
			'facebook-for-woocommerce/facebook-for-woocommerce.php' => 'Facebook for WooCommerce',
			'official-facebook-pixel/facebook-pixel.php' => 'Official Facebook Pixel',
		);

		if ( self::has_active_plugin( $paid_plugins ) ) {
			$channels++;
			$earned_points += 25;
			$stats['paid_channel'] = 'enabled';
		}

		// Social sharing channel (20 points).
		$social_plugins = array(
			'add-to-any/add-to-any.php' => 'AddToAny',
			'shared-counts/shared-counts.php' => 'Shared Counts',
			'social-warfare/social-warfare.php' => 'Social Warfare',
		);

		if ( self::has_active_plugin( $social_plugins ) ) {
			$channels++;
			$earned_points += 20;
			$stats['social_channel'] = 'enabled';
		}

		if ( $channels < 2 ) {
			$issues[] = __( 'Only one or fewer acquisition channels detected', 'wpshadow' );
		}

		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;
		$stats['channels']      = $channels;

		if ( $score < 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your channel diversification scored %s. Relying on a single channel is risky. Diversifying acquisition channels protects your business from algorithm changes and market shifts.', 'wpshadow' ),
					$score_text
				) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/channel-diversification',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}

	/**
	 * Check whether any plugin in a list is active.
	 *
	 * @since  1.6035.1400
	 * @param  array $plugins Plugin map.
	 * @return bool
	 */
	private static function has_active_plugin( array $plugins ): bool {
		foreach ( $plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return true;
			}
		}

		return false;
	}
}
