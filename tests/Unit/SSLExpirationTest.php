<?php
/**
 * Tests for SSL Certificate Expiration Diagnostic
 *
 * @package    WPShadow
 * @subpackage Tests\Unit
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_SSL_Expiration;
use WP_Mock\Tools\TestCase;

/**
 * SSL Certificate Expiration Diagnostic Test Class
 *
 * @since 1.6093.1200
 */
class SSLExpirationTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();

		// Define WordPress constant.
		if ( ! defined( 'DAY_IN_SECONDS' ) ) {
			define( 'DAY_IN_SECONDS', 86400 );
		}
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		parent::tearDown();
	}

	/**
	 * Test diagnostic returns null for HTTP sites
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_for_http_sites(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'http://example.com',
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic returns null when certificate has 60+ days
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_passes_when_certificate_valid_long_term(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 90 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 30 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic flags certificate expiring in 20 days
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_certificate_expiring_in_20_days(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 20 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 70 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'ssl-expiration', $result['id'] );
		$this->assertEquals( 'medium', $result['severity'] );
		$this->assertEquals( 50, $result['threat_level'] );
	}

	/**
	 * Test diagnostic flags certificate expiring in 5 days as critical
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_certificate_expiring_in_5_days_critical(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 5 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 85 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'ssl-expiration', $result['id'] );
		$this->assertEquals( 'critical', $result['severity'] );
		$this->assertEquals( 75, $result['threat_level'] );
	}

	/**
	 * Test diagnostic flags expired certificate
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_flags_expired_certificate(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() - ( 10 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 100 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'SSL certificate expired 10 days ago',
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'ssl-expiration', $result['id'] );
		$this->assertEquals( 'critical', $result['severity'] );
		$this->assertEquals( 90, $result['threat_level'] );
		$this->assertLessThan( 0, $result['meta']['days_remaining'] );
	}

	/**
	 * Test finding includes certificate details
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_finding_includes_certificate_details(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt Authority X3',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 15 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 75 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123DEF456',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'certificate_details', $result['details'] );
		$this->assertEquals( 'Let\'s Encrypt Authority X3', $result['details']['certificate_details']['issuer'] );
		$this->assertEquals( 'example.com', $result['details']['certificate_details']['subject'] );
	}

	/**
	 * Test finding includes meta information
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_finding_includes_meta(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 15 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 75 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'days_remaining', $result['meta'] );
		$this->assertArrayHasKey( 'expiration_date', $result['meta'] );
		$this->assertArrayHasKey( 'issuer', $result['meta'] );
		$this->assertArrayHasKey( 'subject', $result['meta'] );
		$this->assertArrayHasKey( 'certificate_age', $result['meta'] );
	}

	/**
	 * Test finding includes remediation steps
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_finding_includes_remediation_steps(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 15 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 75 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'remediation_steps', $result['details'] );
		$this->assertNotEmpty( $result['details']['remediation_steps'] );
	}

	/**
	 * Test expired certificate has urgent remediation steps
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_expired_certificate_urgent_remediation(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() - ( 5 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 95 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'sprintf', array(
			'return' => 'SSL certificate expired 5 days ago',
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'remediation_steps', $result['details'] );

		// Check for URGENT in first step.
		$first_step = $result['details']['remediation_steps'][0];
		$this->assertStringContainsString( 'URGENT', $first_step );
	}

	/**
	 * Test finding includes impact analysis
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_finding_includes_impact_analysis(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 15 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 75 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'impact', $result['details'] );
		$this->assertArrayHasKey( 'user_experience', $result['details']['impact'] );
		$this->assertArrayHasKey( 'seo', $result['details']['impact'] );
		$this->assertArrayHasKey( 'trust', $result['details']['impact'] );
	}

	/**
	 * Test finding structure is valid
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_finding_structure_valid(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 15 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 75 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertArrayHasKey( 'auto_fixable', $result );
		$this->assertArrayHasKey( 'kb_link', $result );
		$this->assertArrayHasKey( 'family', $result );
		$this->assertArrayHasKey( 'meta', $result );
		$this->assertArrayHasKey( 'details', $result );

		$this->assertEquals( 'ssl-expiration', $result['id'] );
		$this->assertEquals( 'security', $result['family'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test certificate age calculation
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_calculates_certificate_age_correctly(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		// Certificate issued 60 days ago.
		$issued_date = time() - ( 60 * DAY_IN_SECONDS );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 15 * DAY_IN_SECONDS ),
				'issued_date'     => $issued_date,
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertGreaterThanOrEqual( 59, $result['meta']['certificate_age'] );
		$this->assertLessThanOrEqual( 61, $result['meta']['certificate_age'] );
	}

	/**
	 * Test diagnostic handles missing certificate gracefully
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_handles_missing_certificate_gracefully(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => false,
		) );

		\WP_Mock::userFunction( 'wp_parse_url', array(
			'return' => 'example.com',
		) );

		// Mock WP_Error.
		\WP_Mock::userFunction( 'is_wp_error', array(
			'return' => true,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic includes hosting provider tips
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_finding_includes_hosting_provider_tips(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 15 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 75 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'hosting_providers', $result['details'] );
		$this->assertNotEmpty( $result['details']['hosting_providers'] );
	}

	/**
	 * Test description changes based on days remaining
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function test_description_varies_by_urgency(): void {
		\WP_Mock::userFunction( 'get_site_url', array(
			'return' => 'https://example.com',
		) );

		// Test with 1 day remaining.
		\WP_Mock::userFunction( 'get_transient', array(
			'return' => array(
				'issuer'          => 'Let\'s Encrypt',
				'subject'         => 'example.com',
				'expiration_date' => time() + ( 1 * DAY_IN_SECONDS ),
				'issued_date'     => time() - ( 89 * DAY_IN_SECONDS ),
				'serial_number'   => 'ABC123',
			),
		) );

		\WP_Mock::userFunction( '__', array(
			'return' => function( $text ) {
				return $text;
			},
		) );

		\WP_Mock::userFunction( 'gmdate', array(
			'return_arg' => 1,
		) );

		$result = Diagnostic_SSL_Expiration::check();

		$this->assertIsArray( $result );
		$this->assertStringContainsString( 'tomorrow', strtolower( $result['description'] ) );
	}
}
