<?php
/**
 * Tests for REST API User Enumeration Diagnostic
 *
 * @package WPShadow\Tests\Unit
 * @since   1.2802.1445
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_REST_User_Enumeration;
use WPShadow\Tests\TestCase;

/**
 * REST API User Enumeration Diagnostic Tests
 *
 * @since 1.2802.1445
 */
class RestUserEnumerationTest extends TestCase {

	/**
	 * Set up test environment before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		Diagnostic_REST_User_Enumeration::clear_test_rest_response();
	}

	/**
	 * Tear down test environment after each test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		Diagnostic_REST_User_Enumeration::clear_test_rest_response();
	}

	/**
	 * Test diagnostic detects vulnerable REST API (default WordPress config).
	 *
	 * @return void
	 */
	public function testDetectsVulnerableRestApi(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Administrator',
				'slug' => 'admin',
			),
			array(
				'id'   => 2,
				'name' => 'Editor User',
				'slug' => 'editor',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertIsArray( $result );
		$this->assertValidFinding( $result );
		$this->assertEquals( 'rest-user-enumeration', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 65, $result['threat_level'] );
		$this->assertTrue( $result['auto_fixable'] );
		$this->assertEquals( 2, $result['meta']['exposed_users'] );
	}

	/**
	 * Test diagnostic returns null when REST API is secured (401/403).
	 *
	 * @return void
	 */
	public function testReturnsNullWhenRestApiSecured(): void {
		$this->mock_rest_api_error( 401 );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic returns null when REST API returns 403.
	 *
	 * @return void
	 */
	public function testReturnsNullWhenRestApiReturns403(): void {
		$this->mock_rest_api_error( 403 );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic returns null when REST API returns empty array.
	 *
	 * @return void
	 */
	public function testReturnsNullWhenRestApiReturnsEmpty(): void {
		$this->mock_rest_api_response( array() );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic returns null when REST API is completely disabled.
	 *
	 * @return void
	 */
	public function testReturnsNullWhenRestApiDisabled(): void {
		// Simulate REST API not available
		remove_filter( 'rest_url', 'rest_url' );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic handles large number of users (only samples first 5).
	 *
	 * @return void
	 */
	public function testHandlesLargeNumberOfUsers(): void {
		$users = array();
		for ( $i = 1; $i <= 20; $i++ ) {
			$users[] = array(
				'id'   => $i,
				'name' => "User {$i}",
				'slug' => "user{$i}",
			);
		}

		$this->mock_rest_api_response( $users );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 20, $result['meta']['exposed_users'] );
		// Details should only show sample (first 5)
		$this->assertCount( 5, $result['details']['exposed_users_sample'] );
	}

	/**
	 * Test diagnostic with single exposed user.
	 *
	 * @return void
	 */
	public function testDetectsSingleExposedUser(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 1, $result['meta']['exposed_users'] );
	}

	/**
	 * Test finding array structure and required keys.
	 *
	 * @return void
	 */
	public function testFindingArrayStructure(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		// Verify finding has all required fields
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

		// Verify meta has required keys
		$this->assertArrayHasKey( 'exposed_users', $result['meta'] );
		$this->assertArrayHasKey( 'endpoint_url', $result['meta'] );
		$this->assertArrayHasKey( 'rest_api_enabled', $result['meta'] );
	}

	/**
	 * Test KB link is correct.
	 *
	 * @return void
	 */
	public function testKbLinkCorrect(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertEquals(
			'https://wpshadow.com/kb/security-rest-user-enumeration',
			$result['kb_link']
		);
	}

	/**
	 * Test auto_fixable is true (can add filter).
	 *
	 * @return void
	 */
	public function testAutoFixableIsTrue(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertTrue( $result['auto_fixable'] );
	}

	/**
	 * Test severity is high.
	 *
	 * @return void
	 */
	public function testSeverityIsHigh(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 65, $result['threat_level'] );
	}

	/**
	 * Test details include remediation options.
	 *
	 * @return void
	 */
	public function testDetailsIncludeRemediationOptions(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertArrayHasKey( 'remediation_options', $result['details'] );
		$this->assertArrayHasKey( 'Option 1: Add Filter (Quick Fix)', $result['details']['remediation_options'] );
		$this->assertNotEmpty( $result['details']['remediation_options']['Option 1: Add Filter (Quick Fix)']['code'] );
	}

	/**
	 * Test details include attack scenarios.
	 *
	 * @return void
	 */
	public function testDetailsIncludeAttackScenarios(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertArrayHasKey( 'attack_scenarios', $result['details'] );
		$this->assertArrayHasKey( 'Brute Force Attack', $result['details']['attack_scenarios'] );
		$this->assertArrayHasKey( 'Spear Phishing', $result['details']['attack_scenarios'] );
		$this->assertArrayHasKey( 'Social Engineering', $result['details']['attack_scenarios'] );
	}

	/**
	 * Test details include testing instructions.
	 *
	 * @return void
	 */
	public function testDetailsIncludeTestingInstructions(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertArrayHasKey( 'testing_enumeration', $result['details'] );
		$this->assertArrayHasKey( 'Browser Test', $result['details']['testing_enumeration'] );
		$this->assertArrayHasKey( 'Curl Test', $result['details']['testing_enumeration'] );
	}

	/**
	 * Test description contains translatable strings.
	 *
	 * @return void
	 */
	public function testDescriptionHasTranslatableStrings(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertNotEmpty( $result['description'] );
		$this->assertStringContainsString( '1', $result['description'] );
	}

	/**
	 * Test handles malformed user data gracefully.
	 *
	 * @return void
	 */
	public function testHandlesMalformedUserData(): void {
		$this->mock_rest_api_response( array(
			array( 'invalid' => 'data' ),
			null,
			'string',
			array(
				'id'   => 5,
				'name' => 'Valid User',
				'slug' => 'valid',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		// Should still work with partial valid data
		$this->assertIsArray( $result );
		$this->assertGreaterThan( 0, $result['meta']['exposed_users'] );
	}

	/**
	 * Test handles WP_Error from REST API.
	 *
	 * @return void
	 */
	public function testHandlesWpError(): void {
		$this->mock_rest_api_wp_error();

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertNull( $result );
	}

	/**
	 * Test endpoint URL is included in meta.
	 *
	 * @return void
	 */
	public function testEndpointUrlIncludedInMeta(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertArrayHasKey( 'endpoint_url', $result['meta'] );
		$this->assertStringContainsString( 'wp-json/wp/v2/users', $result['meta']['endpoint_url'] );
	}

	/**
	 * Test exposed users sample is formatted correctly.
	 *
	 * @return void
	 */
	public function testExposedUsersSampleFormatted(): void {
		$this->mock_rest_api_response( array(
			array(
				'id'   => 1,
				'name' => 'Admin',
				'slug' => 'admin',
			),
			array(
				'id'   => 2,
				'name' => 'Editor',
				'slug' => 'editor',
			),
		) );

		$result = Diagnostic_REST_User_Enumeration::check();

		$this->assertArrayHasKey( 'exposed_users_sample', $result['details'] );
		$this->assertIsArray( $result['details']['exposed_users_sample'] );
		$this->assertCount( 2, $result['details']['exposed_users_sample'] );
	}

	/**
	 * Helper: Mock REST API response with user data.
	 *
	 * @param array $users Array of user data.
	 * @return void
	 */
	private function mock_rest_api_response( $users ) {
		// Mock REST API to return user data
		add_filter( 'rest_pre_dispatch', function( $result, $server, $request ) use ( $users ) {
			if ( $request->get_route() === '/wp/v2/users' ) {
				return rest_ensure_response( $users );
			}
			return $result;
		}, 10, 3 );
	}

	/**
	 * Helper: Mock REST API error (401/403).
	 *
	 * @param int $status_code HTTP status code.
	 * @return void
	 */
	private function mock_rest_api_error( $status_code ) {
		add_filter( 'rest_pre_dispatch', function( $result, $server, $request ) use ( $status_code ) {
			if ( $request->get_route() === '/wp/v2/users' ) {
				$response = new \WP_REST_Response( array( 'error' => 'Unauthorized' ), $status_code );
				return $response;
			}
			return $result;
		}, 10, 3 );
	}

	/**
	 * Helper: Mock REST API WP_Error.
	 *
	 * @return void
	 */
	private function mock_rest_api_wp_error() {
		add_filter( 'rest_pre_dispatch', function( $result, $server, $request ) {
			if ( $request->get_route() === '/wp/v2/users' ) {
				return new \WP_Error( 'rest_error', 'REST API error' );
			}
			return $result;
		}, 10, 3 );
	}
}
