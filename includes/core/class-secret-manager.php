<?php
/**
 * Secret Manager
 *
 * Handles secure storage and retrieval of sensitive data like API keys.
 * All secrets are encrypted before storage using WordPress salts.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.26032.1000
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Secret Manager Class
 *
 * Provides encryption/decryption for sensitive data storage.
 * Prevents plaintext secrets in database.
 *
 * @since 1.26032.1000
 */
class Secret_Manager {

	const ENCRYPTION_METHOD = 'aes-256-cbc';
	const OPTION_PREFIX = '_encrypted_';

	/**
	 * Store encrypted secret
	 *
	 * @since  1.26032.1000
	 * @param  string $key    Secret key identifier.
	 * @param  string $secret Secret value to encrypt.
	 * @return bool True on success.
	 */
	public static function store( string $key, string $secret ): bool {
		// Don't store empty secrets
		if ( empty( $secret ) ) {
			delete_option( self::OPTION_PREFIX . $key );
			return true;
		}

		try {
			$encryption_key = self::get_encryption_key();
			$encrypted = self::encrypt( $secret, $encryption_key );

			if ( ! $encrypted ) {
				Error_Handler::log_error( 'Failed to encrypt secret: ' . $key );
				return false;
			}

			update_option( self::OPTION_PREFIX . $key, $encrypted );
			return true;
		} catch ( \Exception $e ) {
			Error_Handler::log_error( $e->getMessage(), $e );
			return false;
		}
	}

	/**
	 * Retrieve and decrypt secret
	 *
	 * @since  1.26032.1000
	 * @param  string $key Secret key identifier.
	 * @return string|null Decrypted secret or null if not found.
	 */
	public static function retrieve( string $key ): ?string {
		$encrypted = get_option( self::OPTION_PREFIX . $key );

		if ( empty( $encrypted ) ) {
			return null;
		}

		try {
			$encryption_key = self::get_encryption_key();
			$decrypted = self::decrypt( $encrypted, $encryption_key );

			if ( ! $decrypted ) {
				Error_Handler::log_error( 'Failed to decrypt secret: ' . $key );
				return null;
			}

			return $decrypted;
		} catch ( \Exception $e ) {
			Error_Handler::log_error( $e->getMessage(), $e );
			return null;
		}
	}

	/**
	 * Delete encrypted secret
	 *
	 * @since  1.26032.1000
	 * @param  string $key Secret key identifier.
	 * @return bool True on success.
	 */
	public static function delete( string $key ): bool {
		delete_option( self::OPTION_PREFIX . $key );
		return true;
	}

	/**
	 * Check if secret exists
	 *
	 * @since  1.26032.1000
	 * @param  string $key Secret key identifier.
	 * @return bool True if secret exists.
	 */
	public static function exists( string $key ): bool {
		return false !== get_option( self::OPTION_PREFIX . $key );
	}

	/**
	 * Encrypt data using AES-256-CBC
	 *
	 * @since  1.26032.1000
	 * @param  string $data Data to encrypt.
	 * @param  string $key  Encryption key.
	 * @return string|false Encrypted data or false on failure.
	 */
	private static function encrypt( string $data, string $key ) {
		// Generate random IV (Initialization Vector)
		$iv_length = openssl_cipher_iv_length( self::ENCRYPTION_METHOD );
		if ( ! $iv_length ) {
			return false;
		}

		$iv = openssl_random_pseudo_bytes( $iv_length );
		if ( ! $iv ) {
			return false;
		}

		// Encrypt the data
		$encrypted = openssl_encrypt( $data, self::ENCRYPTION_METHOD, $key, 0, $iv );
		if ( ! $encrypted ) {
			return false;
		}

		// Combine IV + encrypted data and base64 encode
		$combined = $iv . $encrypted;
		return base64_encode( $combined );
	}

	/**
	 * Decrypt data using AES-256-CBC
	 *
	 * @since  1.26032.1000
	 * @param  string $data Encrypted data (base64 encoded).
	 * @param  string $key  Encryption key.
	 * @return string|false Decrypted data or false on failure.
	 */
	private static function decrypt( string $data, string $key ) {
		// Decode from base64
		$combined = base64_decode( $data, true );
		if ( ! $combined ) {
			return false;
		}

		// Extract IV and encrypted data
		$iv_length = openssl_cipher_iv_length( self::ENCRYPTION_METHOD );
		if ( ! $iv_length || strlen( $combined ) < $iv_length ) {
			return false;
		}

		$iv = substr( $combined, 0, $iv_length );
		$encrypted = substr( $combined, $iv_length );

		// Decrypt
		$decrypted = openssl_decrypt( $encrypted, self::ENCRYPTION_METHOD, $key, 0, $iv );
		return $decrypted;
	}

	/**
	 * Get encryption key derived from WordPress salts
	 *
	 * This ensures the key is consistent for the same installation
	 * but unique across different WordPress instances.
	 *
	 * @since  1.26032.1000
	 * @return string Encryption key (256 bits / 32 bytes).
	 */
	private static function get_encryption_key(): string {
		// Use WordPress auth salts to create a unique, consistent key
		$salts = array(
			defined( 'AUTH_KEY' ) ? AUTH_KEY : '',
			defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : '',
			defined( 'LOGGED_IN_KEY' ) ? LOGGED_IN_KEY : '',
			defined( 'NONCE_KEY' ) ? NONCE_KEY : '',
		);

		// Combine salts and hash to get 256-bit key
		$combined = implode( '', $salts );
		$key = hash( 'sha256', $combined, true );

		if ( strlen( $key ) !== 32 ) {
			// Fallback if hash fails
			$key = substr( str_pad( $combined, 32, '0' ), 0, 32 );
		}

		return $key;
	}
}
