<?php
/**
 * Blockchain Integration Diagnostic
 *
 * Tests whether the site integrates blockchain technology for transactions or verification.
 *
 * @since   1.6034.0200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blockchain Integration Diagnostic Class
 *
 * Blockchain technology provides decentralized verification, transparent transactions,
 * and immutable records, useful for supply chain, authentication, and trust building.
 *
 * @since 1.6034.0200
 */
class Diagnostic_Blockchain_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'blockchain-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Blockchain Technology Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site integrates blockchain technology for transactions or verification';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'emerging-technology';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$blockchain_score = 0;
		$max_score = 7;

		// Check for blockchain plugins.
		$blockchain_plugins = array(
			'cryptocurrency-product-for-woocommerce/cryptocurrency-product-for-woocommerce.php' => 'Cryptocurrency Product',
			'blockchain-authentication/blockchain-authentication.php' => 'Blockchain Authentication',
			'web3-wordpress/web3-wordpress.php' => 'Web3 WordPress',
		);

		$has_blockchain_plugin = false;
		foreach ( $blockchain_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_blockchain_plugin = true;
				$blockchain_score++;
				break;
			}
		}

		if ( ! $has_blockchain_plugin ) {
			$issues[] = __( 'No blockchain integration plugin detected', 'wpshadow' );
		}

		// Check for cryptocurrency payment gateways.
		$crypto_gateways = array(
			'coinbase-commerce/coinbase-commerce.php' => 'Coinbase Commerce',
			'woocommerce-gateway-ethereum/woocommerce-gateway-ethereum.php' => 'Ethereum Gateway',
			'woocommerce-bitcoin-payment-gateway/woocommerce-bitcoin-payment-gateway.php' => 'Bitcoin Gateway',
			'mycryptocheckout/mycryptocheckout.php' => 'MyCryptoCheckout',
		);

		$has_crypto_gateway = false;
		foreach ( $crypto_gateways as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_crypto_gateway = true;
				$blockchain_score++;
				break;
			}
		}

		if ( ! $has_crypto_gateway ) {
			$issues[] = __( 'No cryptocurrency payment gateway integrated', 'wpshadow' );
		}

		// Check for smart contract functionality.
		$smart_contracts = self::check_smart_contracts();
		if ( $smart_contracts ) {
			$blockchain_score++;
		} else {
			$issues[] = __( 'No smart contract functionality detected', 'wpshadow' );
		}

		// Check for decentralized identity (DID).
		$decentralized_id = self::check_decentralized_identity();
		if ( $decentralized_id ) {
			$blockchain_score++;
		} else {
			$issues[] = __( 'No decentralized identity system implemented', 'wpshadow' );
		}

		// Check for blockchain verification features.
		$verification_features = self::check_verification_features();
		if ( $verification_features ) {
			$blockchain_score++;
		} else {
			$issues[] = __( 'No blockchain verification features (certificates, authenticity)', 'wpshadow' );
		}

		// Check for distributed storage integration.
		$distributed_storage = self::check_distributed_storage();
		if ( $distributed_storage ) {
			$blockchain_score++;
		} else {
			$issues[] = __( 'No distributed storage integration (IPFS, Arweave)', 'wpshadow' );
		}

		// Check for blockchain content or use cases.
		$blockchain_content = self::check_blockchain_content();
		if ( $blockchain_content ) {
			$blockchain_score++;
		} else {
			$issues[] = __( 'No blockchain-related content or use case documentation', 'wpshadow' );
		}

		// Determine severity based on blockchain implementation.
		$blockchain_percentage = ( $blockchain_score / $max_score ) * 100;

		if ( $blockchain_percentage < 20 ) {
			// Minimal or no blockchain implementation.
			$severity = 'low';
			$threat_level = 20;
		} elseif ( $blockchain_percentage < 50 ) {
			// Basic blockchain implementation.
			$severity = 'low';
			$threat_level = 15;
		} else {
			// Good blockchain implementation - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Blockchain implementation percentage */
				__( 'Blockchain integration at %d%%. ', 'wpshadow' ),
				(int) $blockchain_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Blockchain technology is emerging - evaluate relevance to your business model', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/blockchain-integration',
			);
		}

		return null;
	}

	/**
	 * Check for smart contract functionality.
	 *
	 * @since  1.6034.0200
	 * @return bool True if smart contracts exist, false otherwise.
	 */
	private static function check_smart_contracts() {
		// Check for Web3.js or Ethereum integration.
		global $wp_scripts;

		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( strpos( $handle, 'web3' ) !== false || strpos( $script->src, 'web3' ) !== false ) {
					return true;
				}
				if ( strpos( $handle, 'ethereum' ) !== false || strpos( $script->src, 'ethereum' ) !== false ) {
					return true;
				}
			}
		}

		return apply_filters( 'wpshadow_has_smart_contracts', false );
	}

	/**
	 * Check for decentralized identity systems.
	 *
	 * @since  1.6034.0200
	 * @return bool True if DID system exists, false otherwise.
	 */
	private static function check_decentralized_identity() {
		// Check for DID plugins or wallet login.
		$did_plugins = array(
			'web3-login/web3-login.php',
			'metamask-login/metamask-login.php',
			'did-authentication/did-authentication.php',
		);

		foreach ( $did_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_decentralized_identity', false );
	}

	/**
	 * Check for blockchain verification features.
	 *
	 * @since  1.6034.0200
	 * @return bool True if verification features exist, false otherwise.
	 */
	private static function check_verification_features() {
		// Check for certificate or authenticity verification.
		$verification_plugins = array(
			'blockchain-certificates/blockchain-certificates.php',
			'certify-blockchain/certify-blockchain.php',
		);

		foreach ( $verification_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for custom post types related to certificates.
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		foreach ( $post_types as $post_type ) {
			if ( strpos( strtolower( $post_type ), 'certificate' ) !== false ||
				 strpos( strtolower( $post_type ), 'verification' ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_blockchain_verification', false );
	}

	/**
	 * Check for distributed storage integration.
	 *
	 * @since  1.6034.0200
	 * @return bool True if distributed storage exists, false otherwise.
	 */
	private static function check_distributed_storage() {
		// Check for IPFS or Arweave integration.
		$storage_plugins = array(
			'ipfs-media-uploader/ipfs-media-uploader.php',
			'ipfs-integration/ipfs-integration.php',
		);

		foreach ( $storage_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_distributed_storage', false );
	}

	/**
	 * Check for blockchain-related content.
	 *
	 * @since  1.6034.0200
	 * @return bool True if blockchain content exists, false otherwise.
	 */
	private static function check_blockchain_content() {
		// Check for posts/pages with blockchain keywords.
		$keywords = array( 'blockchain', 'cryptocurrency', 'distributed ledger', 'web3', 'decentralized' );

		foreach ( $keywords as $keyword ) {
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

		return apply_filters( 'wpshadow_has_blockchain_content', false );
	}
}
