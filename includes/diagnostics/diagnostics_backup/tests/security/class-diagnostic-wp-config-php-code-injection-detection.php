<?php
/**
 * wp-config.php Code Injection Detection Diagnostic
 *
 * Scans wp-config.php for malicious code injection and backdoors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26030.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wp-config.php Code Injection Detection Class
 *
 * Tests for code injection.
 *
 * @since 1.26030.0000
 */
class Diagnostic_Wp_Config_Php_Code_Injection_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-config-php-code-injection-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp-config.php Code Injection Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Scans wp-config.php for malicious code injection and backdoors';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26030.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$injection_check = self::check_code_injection();
		
		if ( $injection_check['suspicious_code_found'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $injection_check['findings'] ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-config-php-code-injection-detection',
				'meta'         => array(
					'suspicious_patterns' => $injection_check['suspicious_patterns'],
					'file_size'           => $injection_check['file_size'],
					'recommendations'     => $injection_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check for code injection in wp-config.php.
	 *
	 * @since  1.26030.0000
	 * @return array Check results.
	 */
	private static function check_code_injection() {
		$check = array(
			'suspicious_code_found' => false,
			'findings'              => array(),
			'suspicious_patterns'   => array(),
			'file_size'             => 0,
			'recommendations'       => array(),
		);

		// Get wp-config.php path.
		$config_file = ABSPATH . 'wp-config.php';
		
		if ( ! file_exists( $config_file ) ) {
			// Try one level up.
			$config_file = dirname( ABSPATH ) . '/wp-config.php';
			
			if ( ! file_exists( $config_file ) ) {
				return $check; // Can't find config file.
			}
		}

		// Get file size.
		$check['file_size'] = filesize( $config_file );

		// Read file content.
		$content = file_get_contents( $config_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $content ) {
			return $check;
		}

		// Suspicious patterns to check.
		$suspicious_patterns = array(
			'base64_decode'  => __( 'base64_decode found (often used to hide malicious code)', 'wpshadow' ),
			'eval('          => __( 'eval() found (code execution function - high risk)', 'wpshadow' ),
			'gzinflate'      => __( 'gzinflate found (decompression often used for obfuscation)', 'wpshadow' ),
			'str_rot13'      => __( 'str_rot13 found (obfuscation technique)', 'wpshadow' ),
			'assert('        => __( 'assert() found (can execute arbitrary code)', 'wpshadow' ),
			'preg_replace' . '.*\/e' => __( 'preg_replace with /e modifier (code execution)', 'wpshadow' ),
			'system('        => __( 'system() found (shell command execution)', 'wpshadow' ),
			'exec('          => __( 'exec() found (shell command execution)', 'wpshadow' ),
			'shell_exec'     => __( 'shell_exec found (shell command execution)', 'wpshadow' ),
			'passthru'       => __( 'passthru found (shell command execution)', 'wpshadow' ),
		);

		foreach ( $suspicious_patterns as $pattern => $description ) {
			if ( false !== stripos( $content, $pattern ) ) {
				$check['suspicious_code_found'] = true;
				$check['suspicious_patterns'][] = $pattern;
				$check['findings'][] = $description;
			}
		}

		// Check file size (normal wp-config.php is 3-5KB, bloated is suspicious).
		if ( $check['file_size'] > 10240 ) { // > 10KB.
			$check['suspicious_code_found'] = true;
			$check['findings'][] = sprintf(
				/* translators: %s: file size in KB */
				__( 'wp-config.php is %sKB (unusually large - normal is 3-5KB)', 'wpshadow' ),
				number_format( $check['file_size'] / 1024, 1 )
			);
		}

		// Add recommendations if suspicious code found.
		if ( $check['suspicious_code_found'] ) {
			$check['recommendations'][] = __( 'URGENT: Restore wp-config.php from clean backup immediately', 'wpshadow' );
			$check['recommendations'][] = __( 'Scan entire site for malware with security plugin', 'wpshadow' );
			$check['recommendations'][] = __( 'Change all passwords and security keys after restoration', 'wpshadow' );
			$check['recommendations'][] = __( 'Review server access logs for unauthorized access', 'wpshadow' );
		}

		return $check;
	}
}
