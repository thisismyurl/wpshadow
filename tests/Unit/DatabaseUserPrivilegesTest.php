<?php
/**
 * Tests for Database User Privileges Validation Diagnostic
 *
 * @package WPShadow\Tests\Unit
 * @since   1.2802.1430
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Database_User_Privileges;
use WPShadow\Tests\TestCase;

/**
 * Database User Privileges Diagnostic Tests
 *
 * @since 1.2802.1430
 */
class DatabaseUserPrivilegesTest extends TestCase {

	/**
	 * Set up test environment before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		Diagnostic_Database_User_Privileges::clear_test_privileges();
	}

	/**
	 * Tear down test environment after each test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		Diagnostic_Database_User_Privileges::clear_test_privileges();
	}

	/**
	 * Test diagnostic returns null with minimal privileges (safe configuration).
	 *
	 * @return void
	 */
	public function testReturnsNullWithMinimalPrivileges(): void {
		$this->mock_database_grants( array(
			'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER ON wordpress.* TO \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic detects SUPER privilege (critical).
	 *
	 * @return void
	 */
	public function testDetectsSuperPrivilege(): void {
		$this->mock_database_grants( array(
			'GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' WITH GRANT OPTION',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertIsArray( $result );
		$this->assertValidFinding( $result );
		$this->assertEquals( 'database-user-privileges', $result['id'] );
		$this->assertEquals( 'critical', $result['severity'] );
		$this->assertEquals( 80, $result['threat_level'] );
		$this->assertFalse( $result['auto_fixable'] );
		$this->assertTrue( $result['meta']['has_super'] );
	}

	/**
	 * Test diagnostic detects FILE privilege (high risk).
	 *
	 * @return void
	 */
	public function testDetectsFilePrivilege(): void {
		$this->mock_database_grants( array(
			'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, FILE ON wordpress.* TO \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 50, $result['threat_level'] );
		$this->assertStringContainsString( 'FILE', $result['meta']['excessive_privileges'] );
	}

	/**
	 * Test diagnostic detects PROCESS privilege.
	 *
	 * @return void
	 */
	public function testDetectsProcessPrivilege(): void {
		$this->mock_database_grants( array(
			'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, PROCESS ON wordpress.* TO \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertStringContainsString( 'PROCESS', $result['meta']['excessive_privileges'] );
	}

	/**
	 * Test diagnostic detects RELOAD privilege.
	 *
	 * @return void
	 */
	public function testDetectsReloadPrivilege(): void {
		$this->mock_database_grants( array(
			'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, RELOAD ON wordpress.* TO \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertStringContainsString( 'RELOAD', $result['meta']['excessive_privileges'] );
	}

	/**
	 * Test diagnostic detects multiple excessive privileges.
	 *
	 * @return void
	 */
	public function testDetectsMultipleExcessivePrivileges(): void {
		$this->mock_database_grants( array(
			'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, FILE, PROCESS, RELOAD ON wordpress.* TO \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 65, $result['threat_level'] ); // Multiple excessive = higher threat
		$this->assertStringContainsString( 'FILE', $result['meta']['excessive_privileges'] );
		$this->assertStringContainsString( 'PROCESS', $result['meta']['excessive_privileges'] );
		$this->assertStringContainsString( 'RELOAD', $result['meta']['excessive_privileges'] );
	}

	/**
	 * Test diagnostic handles "ALL PRIVILEGES" grant.
	 *
	 * @return void
	 */
	public function testHandlesAllPrivileges(): void {
		$this->mock_database_grants( array(
			'GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 'critical', $result['severity'] );
		$this->assertTrue( $result['meta']['has_super'] );
	}

	/**
	 * Test diagnostic parses MySQL GRANT format.
	 *
	 * @return void
	 */
	public function testParsesMySqlGrantFormat(): void {
		$this->mock_database_grants( array(
			'GRANT USAGE ON *.* TO \'wpuser\'@\'localhost\'',
			'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER ON `wordpress`.* TO \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		// Should pass (no excessive privileges)
		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic parses MariaDB GRANT format.
	 *
	 * @return void
	 */
	public function testParsesMariaDbGrantFormat(): void {
		$this->mock_database_grants( array(
			'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER ON wordpress.* TO wpuser@localhost IDENTIFIED BY PASSWORD \'***\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		// Should pass (no excessive privileges)
		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic handles column-specific privileges.
	 *
	 * @return void
	 */
	public function testHandlesColumnSpecificPrivileges(): void {
		$this->mock_database_grants( array(
			'GRANT SELECT (id, name), INSERT, UPDATE ON wordpress.wp_users TO \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		// Should pass (SELECT with column restrictions is fine)
		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic handles permission denied gracefully.
	 *
	 * @return void
	 */
	public function testHandlesPermissionDeniedGracefully(): void {
		// Mock database error
		global $wpdb;
		$wpdb->last_error = 'Access denied';

		$result = Diagnostic_Database_User_Privileges::check();

		// Should return null (not a finding, just can't check)
		$this->assertNull( $result );
	}

	/**
	 * Test finding array structure and required keys.
	 *
	 * @return void
	 */
	public function testFindingArrayStructure(): void {
		$this->mock_database_grants( array(
			'GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

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
		$this->assertArrayHasKey( 'excessive_privileges', $result['meta'] );
		$this->assertArrayHasKey( 'has_super', $result['meta'] );
		$this->assertArrayHasKey( 'required_privileges', $result['meta'] );
		$this->assertArrayHasKey( 'database_user', $result['meta'] );
	}

	/**
	 * Test KB link is correct.
	 *
	 * @return void
	 */
	public function testKbLinkCorrect(): void {
		$this->mock_database_grants( array(
			'GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertEquals(
			'https://wpshadow.com/kb/security-database-user-privileges',
			$result['kb_link']
		);
	}

	/**
	 * Test auto_fixable is false (requires hosting provider).
	 *
	 * @return void
	 */
	public function testAutoFixableIsFalse(): void {
		$this->mock_database_grants( array(
			'GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test severity mapping for SUPER privilege.
	 *
	 * @return void
	 */
	public function testSeverityMappingForSuper(): void {
		$this->mock_database_grants( array(
			'GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertEquals( 'critical', $result['severity'] );
		$this->assertEquals( 80, $result['threat_level'] );
	}

	/**
	 * Test severity mapping for single excessive privilege.
	 *
	 * @return void
	 */
	public function testSeverityMappingForSingleExcessive(): void {
		$this->mock_database_grants( array(
			'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, FILE ON wordpress.* TO \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 50, $result['threat_level'] );
	}

	/**
	 * Test severity mapping for multiple excessive privileges.
	 *
	 * @return void
	 */
	public function testSeverityMappingForMultipleExcessive(): void {
		$this->mock_database_grants( array(
			'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, FILE, PROCESS ON wordpress.* TO \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 65, $result['threat_level'] );
	}

	/**
	 * Test details array includes remediation steps.
	 *
	 * @return void
	 */
	public function testDetailsIncludeRemediationSteps(): void {
		$this->mock_database_grants( array(
			'GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertArrayHasKey( 'remediation_steps', $result['details'] );
		$this->assertNotEmpty( $result['details']['remediation_steps'] );
	}

	/**
	 * Test description contains translatable strings.
	 *
	 * @return void
	 */
	public function testDescriptionHasTranslatableStrings(): void {
		$this->mock_database_grants( array(
			'GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		$this->assertNotEmpty( $result['description'] );
		$this->assertIsString( $result['description'] );
	}

	/**
	 * Test handling of empty GRANT results.
	 *
	 * @return void
	 */
	public function testHandlesEmptyGrants(): void {
		$this->mock_database_grants( array() );

		$result = Diagnostic_Database_User_Privileges::check();

		// Should return null (no grants = can't determine privileges)
		$this->assertNull( $result );
	}

	/**
	 * Test handling of malformed GRANT statements.
	 *
	 * @return void
	 */
	public function testHandlesMalformedGrants(): void {
		$this->mock_database_grants( array(
			'INVALID GRANT STATEMENT',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		// Should return null (can't parse = can't determine privileges)
		$this->assertNull( $result );
	}

	/**
	 * Test case insensitivity for privilege names.
	 *
	 * @return void
	 */
	public function testCaseInsensitivityForPrivileges(): void {
		$this->mock_database_grants( array(
			'grant select, insert, update, delete, create, drop, index, alter, super on *.* to \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		// Should detect SUPER even though lowercase in grant
		$this->assertIsArray( $result );
		$this->assertEquals( 'critical', $result['severity'] );
		$this->assertTrue( $result['meta']['has_super'] );
	}

	/**
	 * Test handling of multiple GRANT statements (typical production).
	 *
	 * @return void
	 */
	public function testHandlesMultipleGrantStatements(): void {
		$this->mock_database_grants( array(
			'GRANT USAGE ON *.* TO \'wpuser\'@\'localhost\'',
			'GRANT SELECT, INSERT, UPDATE, DELETE ON `wordpress`.* TO \'wpuser\'@\'localhost\'',
			'GRANT CREATE, DROP, INDEX, ALTER ON `wordpress`.* TO \'wpuser\'@\'localhost\'',
		) );

		$result = Diagnostic_Database_User_Privileges::check();

		// Should pass (has all required, no excessive)
		$this->assertNull( $result );
	}

	/**
	 * Helper: Mock database GRANT results.
	 *
	 * @param array $grants Array of GRANT statement strings.
	 * @return void
	 */
	private function mock_database_grants( $grants ) {
		global $wpdb;

		// Mock wpdb get_results to return grants
		$mock_results = array();
		foreach ( $grants as $grant ) {
			$mock_results[] = array( $grant );
		}

		// Store in test property that diagnostic can access
		$wpdb->test_grants = $mock_results;

		// Override get_results via filter
		add_filter( 'query', function( $query ) use ( $mock_results ) {
			if ( stripos( $query, 'SHOW GRANTS' ) !== false ) {
				global $wpdb;
				$wpdb->last_result = $mock_results;
				return false; // Don't execute real query
			}
			return $query;
		});
	}
}
