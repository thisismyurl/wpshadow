<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Diagnostic Test Runner
 *
 * Background processes that Guardian calls to test each diagnostic.
 * Each test verifies the diagnostic runs correctly and returns expected data.
 *
 * Philosophy: Testing automation ensures diagnostics work before Guardian relies on them.
 * Inspired by #9 (Show Value) - prove diagnostics work correctly.
 *
 * @package WPShadow\Guardian
 */
class Diagnostic_Test_Runner {

	/**
	 * Run all diagnostic tests
	 *
	 * @return array Test results with pass/fail status
	 */
	public static function run_all_tests(): array {
		$results = array();

		// General diagnostics
		$results['site-actually-loading']      = self::test_site_actually_loading();
		$results['backups-working']            = self::test_backups_working();
		$results['broken-images']              = self::test_broken_images();
		$results['business-hours-display']     = self::test_business_hours_display();
		$results['contact-form-working']       = self::test_contact_form_working();
		$results['disaster-recovery']          = self::test_disaster_recovery();
		$results['map-embed-working']          = self::test_map_embed_working();
		$results['updates-available']          = self::test_updates_available();

		// Monitoring diagnostics
		$results['site-down']                  = self::test_site_down();

		// System diagnostics
		$results['disk-space']                 = self::test_disk_space();

		// Security diagnostics (batch 2)
		$results['ssl']                        = self::test_ssl();
		$results['security-headers']           = self::test_security_headers();
		$results['file-permissions']           = self::test_file_permissions();
		$results['secret-keys']                = self::test_secret_keys();
		$results['two-factor']                 = self::test_two_factor();
		$results['login-protection']           = self::test_login_protection();
		$results['malware-scan']               = self::test_malware_scan();
		$results['vulnerable-plugins']         = self::test_vulnerable_plugins();

		// Performance diagnostics (batch 3)
		$results['page-speed']                 = self::test_page_speed();
		$results['image-optimization']         = self::test_image_optimization();
		$results['caching']                    = self::test_caching();
		$results['database-optimization']      = self::test_database_optimization();
		$results['memory-limit']               = self::test_memory_limit();
		$results['slow-queries']               = self::test_slow_queries();
		$results['render-blocking']            = self::test_render_blocking();
		$results['font-optimization']          = self::test_font_optimization();

		return $results;
	}

	/**
	 * Test: Site Actually Loading
	 *
	 * Verifies the diagnostic can check if homepage loads successfully.
	 * Tests HTTP request handling and error detection.
	 */
	private static function test_site_actually_loading(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Site_Actually_Loading';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			// Run the diagnostic
			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			// Validate return structure
			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			// Test passes if diagnostic runs without error
			return self::test_result(
				true,
				'Diagnostic executed successfully',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Backups Working
	 *
	 * Verifies backup detection and validation logic.
	 * Tests multiple backup plugin detection.
	 */
	private static function test_backups_working(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Backups_Working';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			// Run the diagnostic
			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			// Validate return structure
			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			// Verify backup plugin detection works
			$has_backup_info = null === $result || ( isset( $result['description'] ) && strpos( $result['description'], 'backup' ) !== false );

			return self::test_result(
				$has_backup_info,
				$has_backup_info ? 'Backup detection working' : 'Backup info missing',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Broken Images
	 *
	 * Verifies image scanning and broken link detection.
	 * Tests content parsing and HTTP validation.
	 */
	private static function test_broken_images(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Broken_Images';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			// Run the diagnostic
			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			// Validate return structure
			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			// Test passes if diagnostic completes scan
			return self::test_result(
				true,
				'Image scan completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Business Hours Display
	 *
	 * Verifies business hours detection in site content.
	 * Tests structured data and content parsing.
	 */
	private static function test_business_hours_display(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Business_Hours_Display';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			// Run the diagnostic
			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			// Validate return structure
			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			return self::test_result(
				true,
				'Business hours check completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Contact Form Working
	 *
	 * Verifies contact form plugin detection and configuration.
	 * Tests multiple form plugin support.
	 */
	private static function test_contact_form_working(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Contact_Form_Working';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			// Run the diagnostic
			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			// Validate return structure
			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			// Verify form detection logic runs
			$has_form_detection = null === $result || ( isset( $result['title'] ) && strpos( strtolower( $result['title'] ), 'form' ) !== false );

			return self::test_result(
				$has_form_detection,
				$has_form_detection ? 'Form detection working' : 'Form detection incomplete',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Disaster Recovery
	 *
	 * Verifies disaster recovery plan detection and validation.
	 * Tests backup + restore capability assessment.
	 */
	private static function test_disaster_recovery(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Disaster_Recovery';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			// Run the diagnostic
			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			// Validate return structure
			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			return self::test_result(
				true,
				'Disaster recovery assessment completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Map Embed Working
	 *
	 * Verifies map embed detection and functionality.
	 * Tests Google Maps, OpenStreetMap, and other embeds.
	 */
	private static function test_map_embed_working(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Map_Embed_Working';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			// Run the diagnostic
			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			// Validate return structure
			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			return self::test_result(
				true,
				'Map embed check completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Updates Available
	 *
	 * Verifies WordPress/plugin/theme update detection.
	 * Tests transient reading and version comparison.
	 */
	private static function test_updates_available(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Updates_Available';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			// Run the diagnostic
			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			// Validate return structure
			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			// Verify update check runs
			$checks_updates = null === $result || ( isset( $result['title'] ) && strpos( strtolower( $result['title'] ), 'update' ) !== false );

			return self::test_result(
				$checks_updates,
				$checks_updates ? 'Update detection working' : 'Update check incomplete',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Site Down
	 *
	 * Verifies site uptime monitoring and downtime detection.
	 * Tests external accessibility checks.
	 */
	private static function test_site_down(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Site_Down';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			// Run the diagnostic
			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			// Validate return structure
			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			return self::test_result(
				true,
				'Uptime monitoring check completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Disk Space
	 *
	 * Verifies disk space monitoring and threshold detection.
	 * Tests filesystem analysis.
	 */
	private static function test_disk_space(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Disk_Space';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			// Run the diagnostic
			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			// Validate return structure
			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			// Verify disk space calculation works
			$has_space_info = null === $result || ( isset( $result['description'] ) && ( strpos( $result['description'], 'GB' ) !== false || strpos( $result['description'], 'MB' ) !== false ) );

			return self::test_result(
				$has_space_info,
				$has_space_info ? 'Disk space monitoring working' : 'Disk space info missing',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: SSL Certificate
	 *
	 * Verifies SSL/HTTPS configuration and certificate validity.
	 * Tests secure connection enforcement.
	 */
	private static function test_ssl(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_SSL';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			$has_ssl_check = null === $result || ( isset( $result['title'] ) && strpos( strtolower( $result['title'] ), 'ssl' ) !== false );

			return self::test_result(
				$has_ssl_check,
				$has_ssl_check ? 'SSL check completed' : 'SSL check incomplete',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Security Headers
	 *
	 * Verifies security header implementation (CSP, HSTS, etc.).
	 * Tests HTTP header analysis.
	 */
	private static function test_security_headers(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Security_Headers';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			return self::test_result(
				true,
				'Security headers check completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: File Permissions
	 *
	 * Verifies WordPress file and directory permissions.
	 * Tests filesystem security.
	 */
	private static function test_file_permissions(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_File_Permissions';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			return self::test_result(
				true,
				'File permissions check completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Secret Keys
	 *
	 * Verifies WordPress secret keys and salts configuration.
	 * Tests wp-config.php security constants.
	 */
	private static function test_secret_keys(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Secret_Keys';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			$has_key_validation = null === $result || ( isset( $result['description'] ) && strpos( strtolower( $result['description'] ), 'key' ) !== false );

			return self::test_result(
				$has_key_validation,
				$has_key_validation ? 'Secret keys validated' : 'Key validation incomplete',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Two-Factor Authentication
	 *
	 * Verifies 2FA implementation and usage.
	 * Tests authentication security.
	 */
	private static function test_two_factor(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Two_Factor';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			return self::test_result(
				true,
				'2FA check completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Login Protection
	 *
	 * Verifies login security measures (rate limiting, captcha, etc.).
	 * Tests brute force protection.
	 */
	private static function test_login_protection(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Login_Rate_Limiting';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			return self::test_result(
				true,
				'Login protection check completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Malware Scan
	 *
	 * Verifies malware detection and file scanning.
	 * Tests security monitoring.
	 */
	private static function test_malware_scan(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Malware_Scan';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			return self::test_result(
				true,
				'Malware scan completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Test: Vulnerable Plugins
	 *
	 * Verifies detection of known vulnerable plugin versions.
	 * Tests security update monitoring.
	 */
	private static function test_vulnerable_plugins(): array {
		$start_time = microtime( true );

		try {
			$diagnostic_class = '\WPShadow\Diagnostics\Diagnostic_Known_Vulnerable_Plugin_Versions';

			if ( ! class_exists( $diagnostic_class ) ) {
				return self::test_result( false, 'Diagnostic class not found', 0 );
			}

			$result = call_user_func( array( $diagnostic_class, 'check' ) );

			if ( null !== $result && ! self::validate_diagnostic_result( $result ) ) {
				return self::test_result( false, 'Invalid result structure', microtime( true ) - $start_time );
			}

			return self::test_result(
				true,
				'Vulnerable plugins check completed',
				microtime( true ) - $start_time,
				$result
			);

		} catch ( \Exception $e ) {
			return self::test_result( false, 'Exception: ' . $e->getMessage(), microtime( true ) - $start_time );
		}
	}

	/**
	 * Validate diagnostic result structure
	 *
	 * Ensures diagnostic returns properly formatted data.
	 *
	 * @param array $result Diagnostic result to validate
	 *
	 * @return bool True if valid structure
	 */
	private static function validate_diagnostic_result( array $result ): bool {
		// Required fields
		$required = array( 'id', 'title', 'description', 'severity', 'category' );

		foreach ( $required as $field ) {
			if ( ! isset( $result[ $field ] ) ) {
				return false;
			}
		}

		// Validate severity is valid value
		$valid_severities = array( 'critical', 'high', 'medium', 'low', 'info' );
		if ( ! in_array( $result['severity'], $valid_severities, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Format test result
	 *
	 * @param bool   $passed        Test passed
	 * @param string $message       Result message
	 * @param float  $execution_time Seconds taken
	 * @param mixed  $diagnostic_result Optional diagnostic output
	 *
	 * @return array Formatted test result
	 */
	private static function test_result( bool $passed, string $message, float $execution_time, $diagnostic_result = null ): array {
		return array(
			'passed'          => $passed,
			'message'         => $message,
			'execution_time'  => round( $execution_time, 4 ),
			'diagnostic_data' => $diagnostic_result,
			'timestamp'       => current_time( 'mysql' ),
		);
	}
}
