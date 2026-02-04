<?php
/**
 * No Acquisition Channel Diversification Diagnostic
 *
 * Checks if customer acquisition uses diverse channels or relies on one.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since      1.6035.2100
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Acquisition Channel Diversification Diagnostic
 *
 * Detects when business relies on single customer acquisition channel.
 * Dependency on one channel is risk. Google algorithm change, Facebook ads
 * getting expensive, or losing one referral partner kills your growth.
 * Diversified channels provide resilience and options.
 *
 * @since 1.6035.2100
 */
class Diagnostic_No_Acquisition_Channel_Diversification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-acquisition-channel-diversification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Acquisition Channels Diversified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer acquisition uses diverse channels or relies on one';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_channels = self::count_active_acquisition_channels();

		if ( $active_channels < 3 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of active channels */
					__( 'Only %d acquisition channels detected. You\'re dependent on one or two channels—risky. When Google algorithm changes or Facebook ads get expensive, your growth stops. Diversified channels provide resilience. Activate: 1) Organic search (free), 2) Paid search (Google), 3) Social media (organic + paid), 4) Content marketing (blog), 5) Partnerships/referrals, 6) Email marketing. Target 5+ channels.', 'wpshadow' ),
					$active_channels
				),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/acquisition-channel-diversification',
				'details'     => array(
					'active_channels'     => $active_channels,
					'target_channels'     => 5,
					'channel_types'       => self::get_channel_types(),
					'business_impact'     => 'Risk mitigation, growth resilience, stable CAC',
					'recommendation'      => __( 'Build strategy in 3+ acquisition channels for growth resilience', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Count active acquisition channels
	 *
	 * @since  1.6035.2100
	 * @return int Number of active channels
	 */
	private static function count_active_acquisition_channels(): int {
		$channels = 0;

		// Check for organic search (Google Analytics)
		if ( self::check_channel_organic() ) {
			$channels++;
		}

		// Check for paid search (Google Ads)
		if ( self::check_channel_paid_search() ) {
			$channels++;
		}

		// Check for social media
		if ( self::check_channel_social_media() ) {
			$channels++;
		}

		// Check for content/blog
		if ( self::check_channel_content() ) {
			$channels++;
		}

		// Check for email marketing
		if ( self::check_channel_email() ) {
			$channels++;
		}

		// Check for partnerships/referrals
		if ( self::check_channel_partnerships() ) {
			$channels++;
		}

		return $channels;
	}

	/**
	 * Check for organic search channel
	 *
	 * @since  1.6035.2100
	 * @return bool True if organic detected
	 */
	private static function check_channel_organic(): bool {
		// Check for SEO plugins or analytics
		$plugins = get_plugins();

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );

			if ( strpos( $plugin_name, 'seo' ) !== false || strpos( $plugin_name, 'yoast' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for paid search channel
	 *
	 * @since  1.6035.2100
	 * @return bool True if paid search detected
	 */
	private static function check_channel_paid_search(): bool {
		$response = wp_remote_get( home_url( '/' ) );

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			if ( preg_match( '/google.*ads|gtag|gclid/i', $body ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for social media channel
	 *
	 * @since  1.6035.2100
	 * @return bool True if social media detected
	 */
	private static function check_channel_social_media(): bool {
		$response = wp_remote_get( home_url( '/' ) );

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			if ( preg_match( '/facebook|instagram|twitter|linkedin|pinterest|tiktok/i', $body ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for content/blog channel
	 *
	 * @since  1.6035.2100
	 * @return bool True if blog active
	 */
	private static function check_channel_content(): bool {
		$posts = get_posts( array(
			'post_type'      => 'post',
			'numberposts'    => 10,
		) );

		return count( $posts ) >= 5;
	}

	/**
	 * Check for email marketing channel
	 *
	 * @since  1.6035.2100
	 * @return bool True if email detected
	 */
	private static function check_channel_email(): bool {
		$plugins = get_plugins();

		$email_keywords = array( 'email', 'mailchimp', 'convertkit', 'aweber', 'newsletter' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $email_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check for partnership channel
	 *
	 * @since  1.6035.2100
	 * @return bool True if partnerships detected
	 */
	private static function check_channel_partnerships(): bool {
		$plugins = get_plugins();

		$partnership_keywords = array( 'affiliate', 'referral', 'partner' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $partnership_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get channel types
	 *
	 * @since  1.6035.2100
	 * @return array Array of channel types
	 */
	private static function get_channel_types(): array {
		return array(
			array(
				'channel'  => 'Organic Search',
				'how'      => 'Rank for keywords, drive free traffic',
				'effort'   => 'High (ongoing)',
				'timeline' => '6-12 months to see results',
				'cost'     => 'Low (just time)',
			),
			array(
				'channel'  => 'Paid Search (Google Ads)',
				'how'      => 'Bid on keywords, drive immediate traffic',
				'effort'   => 'Medium (ongoing optimization)',
				'timeline' => 'Immediate',
				'cost'     => '$500-$5000+/month',
			),
			array(
				'channel'  => 'Social Media',
				'how'      => 'Content + ads on Facebook, Instagram, LinkedIn',
				'effort'   => 'High (content creation ongoing)',
				'timeline' => '3-6 months organic, immediate paid',
				'cost'     => 'Low organic, $500-$2000+/month paid',
			),
			array(
				'channel'  => 'Content Marketing (Blog)',
				'how'      => 'Blog posts + email to build audience',
				'effort'   => 'High (weekly content)',
				'timeline' => '6-12 months to build traffic',
				'cost'     => 'Low (time) or $500-$2000/month for writers',
			),
			array(
				'channel'  => 'Email Marketing',
				'how'      => 'Build list, send regular valuable emails',
				'effort'   => 'Medium (writing + list growth)',
				'timeline' => '6-12 months to build significant list',
				'cost'     => '$10-$100+/month (email platform)',
			),
			array(
				'channel'  => 'Partnerships & Referrals',
				'how'      => 'Partner businesses refer customers for commission',
				'effort'   => 'Medium (relationship building)',
				'timeline' => '3-6 months to establish',
				'cost'     => 'Commission only (10-30% per referral)',
			),
		);
	}
}
