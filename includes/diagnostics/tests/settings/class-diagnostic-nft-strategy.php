<?php
/**
 * NFT Strategy Diagnostic
 *
 * Tests whether the site implements NFTs for community building or revenue generation.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NFT Strategy Diagnostic Class
 *
 * Non-fungible tokens (NFTs) can be used for digital collectibles, membership access,
 * exclusive content, and community engagement, creating new revenue streams.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Nft_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'nft-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'NFT Strategy Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site implements NFTs for community building or revenue generation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'emerging-technology';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$nft_score = 0;
		$max_score = 6;

		// Check for NFT plugins.
		$nft_plugins = array(
			'nft-maker/nft-maker.php' => 'NFT Maker',
			'wp-nft/wp-nft.php' => 'WP NFT',
			'nft-gallery/nft-gallery.php' => 'NFT Gallery',
			'opensea-nft-viewer/opensea-nft-viewer.php' => 'OpenSea NFT Viewer',
		);

		$has_nft_plugin = false;
		$active_plugin = '';
		foreach ( $nft_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_nft_plugin = true;
				$active_plugin = $plugin_name;
				$nft_score++;
				break;
			}
		}

		if ( ! $has_nft_plugin ) {
			$issues[] = __( 'No NFT plugin detected', 'wpshadow' );
		}

		// Check for cryptocurrency payment support.
		$crypto_payment_plugins = array(
			'cryptocurrency-product-for-woocommerce/cryptocurrency-product-for-woocommerce.php',
			'coinbase-commerce/coinbase-commerce.php',
			'woocommerce-gateway-ethereum/woocommerce-gateway-ethereum.php',
		);

		$has_crypto_payment = false;
		foreach ( $crypto_payment_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_crypto_payment = true;
				$nft_score++;
				break;
			}
		}

		if ( ! $has_crypto_payment ) {
			$issues[] = __( 'No cryptocurrency payment gateway for NFT transactions', 'wpshadow' );
		}

		// Check for NFT-related content.
		$nft_content = self::check_nft_content();
		if ( $nft_content ) {
			$nft_score++;
		} else {
			$issues[] = __( 'No NFT-related content or collections found', 'wpshadow' );
		}

		// Check for digital wallet integration.
		$wallet_integration = self::check_wallet_integration();
		if ( $wallet_integration ) {
			$nft_score++;
		} else {
			$issues[] = __( 'No digital wallet integration (MetaMask, WalletConnect, etc.)', 'wpshadow' );
		}

		// Check for smart contract integration.
		$smart_contract = self::check_smart_contract_integration();
		if ( $smart_contract ) {
			$nft_score++;
		} else {
			$issues[] = __( 'No smart contract integration detected', 'wpshadow' );
		}

		// Check for NFT marketplace features.
		$marketplace_features = self::check_marketplace_features();
		if ( $marketplace_features ) {
			$nft_score++;
		} else {
			$issues[] = __( 'No NFT marketplace features (minting, trading, auctions)', 'wpshadow' );
		}

		// Determine severity based on NFT implementation.
		$nft_percentage = ( $nft_score / $max_score ) * 100;

		if ( $nft_percentage < 20 ) {
			// Minimal or no NFT implementation.
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $nft_percentage < 50 ) {
			// Basic NFT implementation.
			$severity = 'low';
			$threat_level = 20;
		} else {
			// Good NFT implementation - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: NFT implementation percentage */
				__( 'NFT implementation at %d%%. ', 'wpshadow' ),
				(int) $nft_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Consider if NFT strategy aligns with your business model', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/nft-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check for NFT-related content.
	 *
	 * @since 0.6093.1200
	 * @return bool True if NFT content exists, false otherwise.
	 */
	private static function check_nft_content() {
		// Check for posts/pages with NFT keywords.
		$nft_keywords = array( 'nft', 'non-fungible', 'token', 'collectible', 'crypto art' );

		foreach ( $nft_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		// Check for custom post types related to NFTs.
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		foreach ( $post_types as $post_type ) {
			if ( strpos( strtolower( $post_type ), 'nft' ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_nft_content', false );
	}

	/**
	 * Check for wallet integration.
	 *
	 * @since 0.6093.1200
	 * @return bool True if wallet integration exists, false otherwise.
	 */
	private static function check_wallet_integration() {
		// Check for wallet connection plugins.
		$wallet_plugins = array(
			'web3-login/web3-login.php',
			'metamask-login/metamask-login.php',
		);

		foreach ( $wallet_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_wallet_integration', false );
	}

	/**
	 * Check for smart contract integration.
	 *
	 * @since 0.6093.1200
	 * @return bool True if smart contract integration exists, false otherwise.
	 */
	private static function check_smart_contract_integration() {
		// Check for web3 or Ethereum integration.
		return apply_filters( 'wpshadow_has_smart_contract_integration', false );
	}

	/**
	 * Check for marketplace features.
	 *
	 * @since 0.6093.1200
	 * @return bool True if marketplace features exist, false otherwise.
	 */
	private static function check_marketplace_features() {
		// Check for WooCommerce with NFT products.
		if ( class_exists( 'WooCommerce' ) ) {
			$products = wc_get_products(
				array(
					'limit'  => 1,
					'status' => 'publish',
				)
			);

			if ( ! empty( $products ) ) {
				// Check if products have NFT-related metadata.
				foreach ( $products as $product ) {
					$description = $product->get_description() . ' ' . $product->get_short_description();
					if ( stripos( $description, 'nft' ) !== false ) {
						return true;
					}
				}
			}
		}

		return apply_filters( 'wpshadow_has_nft_marketplace_features', false );
	}
}
