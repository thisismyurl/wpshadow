<?php
/**
 * Exposed Sensitive Files Diagnostic
 *
 * Checks for publicly accessible sensitive configuration files that should
 * never be exposed, such as .env, .git/config, backup.sql, etc.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Exposed_Sensitive_Files Class
 *
 * Scans for publicly accessible sensitive files that could leak credentials,
 * source code, or database backups.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Exposed_Sensitive_Files extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'exposed-sensitive-files';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Exposed Sensitive Configuration Files';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects publicly accessible sensitive files';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Sensitive files to check
	 *
	 * @var array
	 */
	const SENSITIVE_FILES = array(
		'/.env'                      => 'Environment configuration with database credentials',
		'/.env.local'                => 'Local environment overrides',
		'/.env.production'           => 'Production environment variables',
		'/.git/config'               => 'Git configuration with potential credentials',
		'/.gitignore'                => 'Git ignore patterns (information disclosure)',
		'/backup.sql'                => 'Database backup file',
		'/backup.tar.gz'             => 'Full backup archive',
		'/db.sql'                    => 'Database export',
		'/wp-config.php.bak'         => 'WordPress configuration backup',
		'/wp-config.old'             => 'Old WordPress configuration',
		'/config.php.bak'            => 'Configuration backup',
		'/.htaccess.bak'             => 'Apache configuration backup',
		'/README.md'                 => 'Project documentation with version info',
		'/CHANGELOG.md'              => 'Version and change history',
		'/composer.json'             => 'PHP dependencies (version info disclosure)',
		'/package.json'              => 'Node dependencies (version info disclosure)',
		'/.env.example'              => 'Environment template with structure hints',
		'/.well-known/security.txt'  => 'Security contact information',
		'/admin.php'                 => 'Admin access point',
		'/administrator/'            => 'Alternative admin directory',
		'/xmlrpc.php'                => 'XML-RPC endpoint enabled',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if exposed files found, null otherwise.
	 */
	public static function check() {
		$exposed_files = self::scan_for_exposed_files();

		if ( empty( $exposed_files ) ) {
			// No exposed sensitive files detected
			return null;
		}

		$count = count( $exposed_files );

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: number of exposed files */
				__( 'Found %d publicly accessible sensitive %s. Credentials and source code may be compromised.', 'wpshadow' ),
				$count,
				( $count === 1 ? __( 'file', 'wpshadow' ) : __( 'files', 'wpshadow' ) )
			),
			'severity'      => 'critical',
			'threat_level'  => 98,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/exposed-sensitive-files',
			'family'        => self::$family,
			'meta'          => array(
				'exposed_file_count' => $count,
				'exposed_files'      => array_slice( $exposed_files, 0, 10 ), // Show first 10
				'data_at_risk'       => array(
					__( 'Database credentials' ),
					__( 'API keys and secrets' ),
					__( 'Git repository source code' ),
					__( 'Database backups with user data' ),
					__( 'Configuration with sensitive settings' ),
					__( 'Deployment information' ),
				),
				'immediate_actions'  => array(
					__( 'Remove all exposed files from web root immediately', 'wpshadow' ),
					__( 'Assume all exposed credentials are compromised - rotate them', 'wpshadow' ),
					__( 'Review web server access logs for unauthorized downloads', 'wpshadow' ),
					__( 'Check database for unauthorized user accounts added by attackers', 'wpshadow' ),
					__( 'Scan entire site for injected backdoor code', 'wpshadow' ),
					__( 'Monitor for unauthorized access attempts using exposed credentials', 'wpshadow' ),
				),
			),
			'details'       => array(
				'issue'                 => sprintf(
					/* translators: %d: number of exposed files */
					__( '%d sensitive files are accessible to the public internet.', 'wpshadow' ),
					$count
				),
				'security_impact'       => __( 'CATASTROPHIC - Exposed database backups, credentials, and source code provide complete site compromise.', 'wpshadow' ),
				'data_at_risk_details'  => array(
					'.env / .env.* files' => array(
						__( 'Database host, username, password' ),
						__( 'API keys for third-party services' ),
						__( 'Authentication secrets and salts' ),
						__( 'Email credentials' ),
						__( 'Payment processor keys' ),
					),
					'Backup files (*.sql, *.tar.gz)' => array(
						__( 'Complete database dumps with user data, posts, comments' ),
						__( 'Personally identifiable information (PII)' ),
						__( 'Email addresses, usernames, password hashes' ),
						__( 'Private communications and sensitive content' ),
					),
					'.git/config' => array(
						__( 'Full Git history and source code' ),
						__( 'Commit history revealing developers and changes' ),
						__( 'Potential credentials in commit messages' ),
						__( 'Unreleased features and security fixes' ),
					),
					'WordPress config files' => array(
						__( 'Database credentials' ),
						__( 'Authentication keys and salts' ),
						__( 'Debug settings' ),
						__( 'Table prefix patterns' ),
					),
				),
				'remediation'           => array(
					'Step 1: Remove exposed files' => array(
						__( 'Delete all /.env, /.git, /backup.* files from web root' ),
						__( 'Use FTP or your hosting control panel file manager' ),
						__( 'Verify files are no longer accessible via web browser' ),
					),
					'Step 2: Secure web server' => array(
						__( 'Configure Apache to block sensitive files (.htaccess)' ),
						__( 'Configure Nginx to block sensitive files (server block)' ),
						__( 'Block access to hidden files and version control' ),
					),
					'Step 3: Rotate credentials' => array(
						__( 'Change all database passwords' ),
						__( 'Regenerate API keys and authentication tokens' ),
						__( 'Update email account passwords if stored' ),
						__( 'Reset payment processor keys' ),
					),
					'Step 4: Investigate damage' => array(
						__( 'Review access logs for who downloaded the files' ),
						__( 'Check database for new user accounts created' ),
						__( 'Scan for backdoor files or malicious code' ),
						__( 'Monitor for unauthorized login attempts' ),
					),
				),
			),
		);
	}

	/**
	 * Scan for exposed sensitive files.
	 *
	 * @since  1.2601.2148
	 * @return array List of exposed files that are publicly accessible.
	 */
	private static function scan_for_exposed_files() {
		$exposed = array();
		$home_url = home_url();

		foreach ( self::SENSITIVE_FILES as $file => $description ) {
			// Build full URL
			$url = $home_url . $file;

			// Check if file is publicly accessible
			$response = wp_remote_head(
				$url,
				array(
					'timeout'   => 5,
					'sslverify' => true,
					'blocking'  => true,
				)
			);

			if ( is_wp_error( $response ) ) {
				// Connection error - assume not accessible
				continue;
			}

			$status_code = wp_remote_retrieve_response_code( $response );

			if ( 200 === $status_code ) {
				// File is publicly accessible
				$exposed[] = array(
					'file'        => $file,
					'url'         => $url,
					'description' => $description,
					'status_code' => $status_code,
					'risk'        => 'critical',
				);
			}
		}

		return $exposed;
	}
}
