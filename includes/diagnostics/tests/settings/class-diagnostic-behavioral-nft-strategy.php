<?php
/**
 * Diagnostic: NFT Strategy
 *
 * Tests whether the site implements NFTs (non-fungible tokens) for community
 * building, digital collectibles, or exclusive access.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4558
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NFT Strategy Diagnostic
 *
 * Checks for NFT implementation. NFTs enable digital ownership, community
 * membership tokens, exclusive access, and creator revenue streams.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_NFT_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-nft-strategy';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'NFT Strategy';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site implements NFTs for community/revenue';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for NFT implementation.
	 *
	 * Looks for NFT minting, sales, and community features.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for NFT-related plugins.
		$all_plugins = get_plugins();
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( preg_match( '/(nft|non-fungible)/i', $plugin_data['Name'] ) ) {
				if ( is_plugin_active( $plugin_file ) ) {
					return null; // Has NFT functionality.
				}
			}
		}

		// Check content for NFT keywords.
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page', 'product' ),
				'posts_per_page' => 30,
				'post_status'    => 'publish',
			)
		);

		foreach ( $posts as $post ) {
			if ( preg_match( '/(nft|non-fungible|mint|opensea|rarible)/i', $post->post_content ) ) {
				return null; // Site discusses/sells NFTs.
			}
		}

		// Only recommend for creators/communities where NFTs add value.
		$nft_relevant = false;
		
		// Digital art/creative sites.
		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
			$nft_relevant = true; // Digital products.
		}

		// Membership/community sites.
		$membership_indicators = array(
			class_exists( 'MeprUser' ),
			function_exists( 'pmpro_hasMembershipLevel' ),
			class_exists( 'BuddyPress' ),
		);

		foreach ( $membership_indicators as $indicator ) {
			if ( $indicator ) {
				$nft_relevant = true;
				break;
			}
		}

		// Check site for creative/community focus.
		$site_name = get_bloginfo( 'name' );
		$site_desc = get_bloginfo( 'description' );
		$creative_keywords = array( 'art', 'creator', 'artist', 'community', 'collective', 'gallery' );
		
		foreach ( $creative_keywords as $keyword ) {
			if ( stripos( $site_name, $keyword ) !== false || stripos( $site_desc, $keyword ) !== false ) {
				$nft_relevant = true;
				break;
			}
		}

		if ( ! $nft_relevant ) {
			return null; // NFTs not relevant for this site.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No NFT strategy detected for creator/community site. NFTs (non-fungible tokens) enable: digital collectibles with proven ownership, community membership tokens (token-gated content), exclusive access tiers, creator revenue from resales (royalties). NFTs build engaged communities and new revenue streams. Consider NFT membership tokens or digital collectibles for loyal community members.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/nft-strategy',
		);
	}
}
