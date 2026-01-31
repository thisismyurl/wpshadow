<?php
/**
 * FTP/SSH Constants Security Review Diagnostic
 *
 * Checks for insecure FTP/SSH credentials stored in wp-config.php.
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
 * FTP/SSH Constants Security Review Class
 *
 * Tests FTP/SSH security.
 *
 * @since 1.26030.0000
 */
class Diagnostic_Ftp_Ssh_Constants_Security_Review extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ftp-ssh-constants-security-review';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'FTP/SSH Constants Security Review';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for insecure FTP/SSH credentials stored in wp-config.php';

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
		$ftp_check = self::check_ftp_ssh_constants();
		
		if ( $ftp_check['has_security_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $ftp_check['issues'] ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ftp-ssh-constants-security-review',
				'meta'         => array(
					'ftp_credentials_present' => $ftp_check['ftp_credentials_present'],
					'fs_method'               => $ftp_check['fs_method'],
					'recommendations'         => $ftp_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check FTP/SSH constants security.
	 *
	 * @since  1.26030.0000
	 * @return array Check results.
	 */
	private static function check_ftp_ssh_constants() {
		$check = array(
			'has_security_issues'     => false,
			'issues'                  => array(),
			'ftp_credentials_present' => false,
			'fs_method'               => defined( 'FS_METHOD' ) ? FS_METHOD : 'auto',
			'recommendations'         => array(),
		);

		// Check for FTP credentials in constants.
		if ( defined( 'FTP_USER' ) && ! empty( FTP_USER ) ) {
			$check['ftp_credentials_present'] = true;
			$check['has_security_issues'] = true;
			$check['issues'][] = __( 'FTP credentials stored in wp-config.php (major security risk - anyone reading config gets FTP access)', 'wpshadow' );
			$check['recommendations'][] = __( 'Remove FTP credentials and use FS_METHOD direct or SSH keys', 'wpshadow' );
		}

		if ( defined( 'FTP_PASS' ) && ! empty( FTP_PASS ) ) {
			$check['ftp_credentials_present'] = true;
			$check['has_security_issues'] = true;
			$check['issues'][] = __( 'Plain text FTP password in wp-config.php (unacceptable security risk)', 'wpshadow' );
			$check['recommendations'][] = __( 'Remove FTP_PASS constant immediately and use secure file permissions instead', 'wpshadow' );
		}

		// Check FS_METHOD.
		if ( defined( 'FS_METHOD' ) && 'ftpext' === FS_METHOD ) {
			$check['has_security_issues'] = true;
			$check['issues'][] = __( 'FS_METHOD set to FTP (insecure - should use direct or ssh2)', 'wpshadow' );
			$check['recommendations'][] = __( 'Change FS_METHOD to direct if file permissions allow, or use SSH', 'wpshadow' );
		}

		// Check for FTP_HOST using non-secure FTP.
		if ( defined( 'FTP_HOST' ) && ! empty( FTP_HOST ) ) {
			$ftp_host = FTP_HOST;
			
			// Check if it's standard FTP (not SFTP).
			if ( false === strpos( $ftp_host, 'sftp' ) && false === strpos( $ftp_host, ':22' ) ) {
				$check['has_security_issues'] = true;
				$check['issues'][] = __( 'FTP_HOST using unsecured FTP instead of SFTP (credentials sent unencrypted)', 'wpshadow' );
				$check['recommendations'][] = __( 'Switch to SFTP (SSH File Transfer Protocol) for encrypted connections', 'wpshadow' );
			}
		}

		return $check;
	}
}
