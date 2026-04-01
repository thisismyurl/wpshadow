<?php
/**
 * Diagnostic: Blockchain Integration
 *
 * Tests whether the site integrates blockchain technology for secure
 * transactions, verification, or decentralized functionality.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4557
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blockchain Integration Diagnostic
 *
 * Checks for blockchain/crypto features. Blockchain enables cryptocurrency
 * payments, smart contracts, verification, and decentralized functionality.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Behavioral_Blockchain extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'integrates-blockchain-technology';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Blockchain Integration';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site integrates blockchain for transactions/verification';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for blockchain implementation.
	 *
	 * Looks for cryptocurrency payment gateways and blockchain features.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for crypto payment plugins.
		$crypto_plugins = array(
			'woocommerce-gateway-bitcoin/woocommerce-gateway-bitcoin.php' => 'Bitcoin',
			'coinbase-commerce/coinbase-commerce.php'                     => 'Coinbase Commerce',
		);

		foreach ( $crypto_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has crypto integration.
			}
		}

		// Check for Web3/blockchain keywords in plugins.
		$all_plugins = get_plugins();
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( preg_match( '/(crypto|blockchain|web3|ethereum|bitcoin)/i', $plugin_data['Name'] ) ) {
				if ( is_plugin_active( $plugin_file ) ) {
					return null;
				}
			}
		}

		// Only recommend for sites where blockchain adds value.
		$needs_blockchain = false;

		// E-commerce accepting international payments.
		if ( class_exists( 'WooCommerce' ) ) {
			// Crypto useful for international e-commerce.
			$needs_blockchain = true;
		}

		// Digital products/NFTs.
		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
			$needs_blockchain = true;
		}

		if ( ! $needs_blockchain ) {
			return null; // Blockchain not applicable.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No blockchain integration detected. Cryptocurrency payment options reach global customers without currency conversion fees or international payment restrictions. Blockchain enables: crypto payments (Bitcoin, Ethereum), smart contracts for automated transactions, NFT sales for digital products, decentralized verification. Consider Coinbase Commerce for simple crypto payments.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 18,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/blockchain-integration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}
}
