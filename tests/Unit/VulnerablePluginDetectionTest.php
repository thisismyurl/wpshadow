<?php
/**
 * Tests for Vulnerable Plugin Detection Diagnostic
 *
 * @package WPShadow\Tests\Unit
 * @since   1.2802.1430
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Vulnerable_Plugin_Detection;
use WPShadow\Tests\TestCase;

/**
 * Vulnerable Plugin Detection Diagnostic Tests
 *
 * @since 1.2802.1430
 */
class VulnerablePluginDetectionTest extends TestCase {

	/**
	 * Set up test environment before each test.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		// Clear any cached CVE data
		Diagnostic_Vulnerable_Plugin_Detection::clear_cve_cache();
	}

	/**
	 * Tear down test environment after each test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
		// Clean up cached CVE data
		Diagnostic_Vulnerable_Plugin_Detection::clear_cve_cache();
	}

	/**
	 * Test diagnostic returns null when no plugins installed.
	 *
	 * @return void
	 */
	public function testReturnsNullWhenNoPluginsInstalled(): void {
		// Mock empty plugins list
		add_filter( 'plugins_list', function() {
			return array();
		});

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic returns null when no vulnerable plugins found.
	 *
	 * @return void
	 */
	public function testReturnsNullWhenNoVulnerabilitiesFound(): void {
		// Mock safe plugins
		$this->mock_plugins( array(
			'akismet/akismet.php' => array(
				'Name'    => 'Akismet',
				'Version' => '5.3',
			),
		));

		// Set empty CVE database
		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( array() );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic detects vulnerable plugin with single CVE.
	 *
	 * @return void
	 */
	public function testDetectsSingleVulnerablePlugin(): void {
		// Mock vulnerable plugin
		$this->mock_plugins( array(
			'vulnerable-plugin/vulnerable-plugin.php' => array(
				'Name'    => 'Vulnerable Plugin',
				'Version' => '1.0.0',
			),
		));

		// Mock CVE database with vulnerability
		$cve_database = array(
			'vulnerable-plugin' => array(
				array(
					'id'                  => 'CVE-2024-1234',
					'title'               => 'Security vulnerability in Vulnerable Plugin',
					'description'         => 'Test vulnerability',
					'severity'            => 8.5,
					'affected_versions'   => array( '1.0', '1.0.0' ),
					'fixed_in'            => '1.1.0',
					'published'           => '2024-01-15',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		// Should find vulnerability
		$this->assertIsArray( $result );
		$this->assertValidFinding( $result );
		$this->assertEquals( 'vulnerable-plugin-detection', $result['id'] );
		$this->assertEquals( 'high', $result['severity'] );
		$this->assertEquals( 75, $result['threat_level'] );
		$this->assertFalse( $result['auto_fixable'] );

		// Check meta data
		$this->assertEquals( 1, $result['meta']['vulnerable_plugins'] );
		$this->assertEquals( 1, $result['meta']['total_vulnerabilities'] );
	}

	/**
	 * Test diagnostic detects multiple vulnerable plugins.
	 *
	 * @return void
	 */
	public function testDetectsMultipleVulnerablePlugins(): void {
		// Mock multiple vulnerable plugins
		$this->mock_plugins( array(
			'plugin-one/plugin-one.php' => array(
				'Name'    => 'Plugin One',
				'Version' => '2.0.0',
			),
			'plugin-two/plugin-two.php' => array(
				'Name'    => 'Plugin Two',
				'Version' => '1.5.0',
			),
			'safe-plugin/safe-plugin.php' => array(
				'Name'    => 'Safe Plugin',
				'Version' => '3.0.0',
			),
		));

		// Mock CVE database with multiple vulnerabilities
		$cve_database = array(
			'plugin-one' => array(
				array(
					'id'                => 'CVE-2024-5678',
					'severity'          => 7.2,
					'affected_versions' => array( '2.0' ),
					'fixed_in'          => '2.1.0',
				),
			),
			'plugin-two' => array(
				array(
					'id'                => 'CVE-2024-9999',
					'severity'          => 9.1,
					'affected_versions' => array( '1.5' ),
					'fixed_in'          => '1.6.0',
				),
				array(
					'id'                => 'CVE-2024-8888',
					'severity'          => 6.5,
					'affected_versions' => array( '1.4', '1.5' ),
					'fixed_in'          => '1.6.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		// Should find multiple vulnerabilities
		$this->assertIsArray( $result );
		$this->assertEquals( 2, $result['meta']['vulnerable_plugins'] );
		$this->assertEquals( 3, $result['meta']['total_vulnerabilities'] );
		$this->assertEquals( 1, $result['meta']['critical_vulnerabilities'] );
		$this->assertEquals( 1, $result['meta']['high_vulnerabilities'] );
	}

	/**
	 * Test diagnostic correctly identifies critical severity.
	 *
	 * @return void
	 */
	public function testIdentifiesCriticalSeverity(): void {
		// Mock plugin with 3+ vulnerabilities
		$this->mock_plugins( array(
			'critical-plugin/critical-plugin.php' => array(
				'Name'    => 'Critical Plugin',
				'Version' => '1.0.0',
			),
		));

		// Mock high-severity CVEs
		$cve_database = array(
			'critical-plugin' => array(
				array(
					'id'                => 'CVE-2024-0001',
					'severity'          => 9.8,
					'affected_versions' => array( '1.0' ),
					'fixed_in'          => '1.1.0',
				),
				array(
					'id'                => 'CVE-2024-0002',
					'severity'          => 9.5,
					'affected_versions' => array( '1.0' ),
					'fixed_in'          => '1.1.0',
				),
				array(
					'id'                => 'CVE-2024-0003',
					'severity'          => 7.8,
					'affected_versions' => array( '1.0' ),
					'fixed_in'          => '1.1.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		// Should be critical
		$this->assertEquals( 'critical', $result['severity'] );
		$this->assertEquals( 95, $result['threat_level'] );
		$this->assertEquals( 2, $result['meta']['critical_vulnerabilities'] );
	}

	/**
	 * Test version matching for exact versions.
	 *
	 * @return void
	 */
	public function testVersionMatchingExact(): void {
		$this->mock_plugins( array(
			'test-plugin/test-plugin.php' => array(
				'Name'    => 'Test Plugin',
				'Version' => '2.5.3',
			),
		));

		$cve_database = array(
			'test-plugin' => array(
				array(
					'id'                => 'CVE-TEST-001',
					'severity'          => 7.0,
					'affected_versions' => array( '2.5.3' ),
					'fixed_in'          => '2.5.4',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		// Should find vulnerability for exact version match
		$this->assertIsArray( $result );
		$this->assertEquals( 1, $result['meta']['vulnerable_plugins'] );
	}

	/**
	 * Test version matching for prefix versions (e.g., "2.5" matches "2.5.3").
	 *
	 * @return void
	 */
	public function testVersionMatchingPrefix(): void {
		$this->mock_plugins( array(
			'test-plugin/test-plugin.php' => array(
				'Name'    => 'Test Plugin',
				'Version' => '3.2.1',
			),
		));

		$cve_database = array(
			'test-plugin' => array(
				array(
					'id'                => 'CVE-TEST-002',
					'severity'          => 6.0,
					'affected_versions' => array( '3.2' ), // Matches 3.2.x
					'fixed_in'          => '3.3.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		// Should find vulnerability for prefix match
		$this->assertIsArray( $result );
		$this->assertEquals( 1, $result['meta']['vulnerable_plugins'] );
	}

	/**
	 * Test fixed_in version comparison.
	 *
	 * @return void
	 */
	public function testFixedInVersionComparison(): void {
		// Installed version 1.5.0, fixed in 1.6.0 = vulnerable
		$this->mock_plugins( array(
			'test-plugin/test-plugin.php' => array(
				'Name'    => 'Test Plugin',
				'Version' => '1.5.0',
			),
		));

		$cve_database = array(
			'test-plugin' => array(
				array(
					'id'       => 'CVE-TEST-003',
					'severity' => 8.0,
					'fixed_in' => '1.6.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		// Should detect vulnerability
		$this->assertIsArray( $result );
	}

	/**
	 * Test that installed version equal to fixed_in is not vulnerable.
	 *
	 * @return void
	 */
	public function testFixedVersionIsNotVulnerable(): void {
		$this->mock_plugins( array(
			'test-plugin/test-plugin.php' => array(
				'Name'    => 'Test Plugin',
				'Version' => '2.0.0', // Already fixed
			),
		));

		$cve_database = array(
			'test-plugin' => array(
				array(
					'id'       => 'CVE-TEST-004',
					'severity' => 8.0,
					'fixed_in' => '2.0.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		// Should NOT find vulnerability (version is fixed)
		$this->assertNull( $result );
	}

	/**
	 * Test handling of plugins with missing version data.
	 *
	 * @return void
	 */
	public function testHandlesMissingVersionData(): void {
		$this->mock_plugins( array(
			'no-version-plugin/plugin.php' => array(
				'Name' => 'No Version Plugin',
				// Version field missing
			),
		));

		$cve_database = array(
			'no-version-plugin' => array(
				array(
					'id'       => 'CVE-TEST-005',
					'severity' => 7.0,
					'fixed_in' => '1.0.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		// Should not throw error
		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		// Default version '0.0.0' is less than '1.0.0', so should be vulnerable
		$this->assertIsArray( $result );
	}

	/**
	 * Test finding array structure and required keys.
	 *
	 * @return void
	 */
	public function testFindingArrayStructure(): void {
		$this->mock_plugins( array(
			'test-plugin/test-plugin.php' => array(
				'Name'    => 'Test Plugin',
				'Version' => '1.0.0',
			),
		));

		$cve_database = array(
			'test-plugin' => array(
				array(
					'id'       => 'CVE-TEST-006',
					'severity' => 7.5,
					'fixed_in' => '2.0.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

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
		$this->assertArrayHasKey( 'total_plugins', $result['meta'] );
		$this->assertArrayHasKey( 'vulnerable_plugins', $result['meta'] );
		$this->assertArrayHasKey( 'total_vulnerabilities', $result['meta'] );
		$this->assertArrayHasKey( 'critical_vulnerabilities', $result['meta'] );
		$this->assertArrayHasKey( 'high_vulnerabilities', $result['meta'] );
	}

	/**
	 * Test KB link is correct.
	 *
	 * @return void
	 */
	public function testKbLinkCorrect(): void {
		$this->mock_plugins( array(
			'test-plugin/test-plugin.php' => array(
				'Name'    => 'Test Plugin',
				'Version' => '1.0.0',
			),
		));

		$cve_database = array(
			'test-plugin' => array(
				array(
					'id'       => 'CVE-TEST-007',
					'severity' => 7.0,
					'fixed_in' => '2.0.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		$this->assertEquals(
			'https://wpshadow.com/kb/security-vulnerable-plugin-detection',
			$result['kb_link']
		);
	}

	/**
	 * Test auto_fixable is false (user must approve updates).
	 *
	 * @return void
	 */
	public function testAutoFixableIsFalse(): void {
		$this->mock_plugins( array(
			'test-plugin/test-plugin.php' => array(
				'Name'    => 'Test Plugin',
				'Version' => '1.0.0',
			),
		));

		$cve_database = array(
			'test-plugin' => array(
				array(
					'id'       => 'CVE-TEST-008',
					'severity' => 8.0,
					'fixed_in' => '2.0.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test diagnostic with complex plugin structure (sub-plugins).
	 *
	 * @return void
	 */
	public function testHandlesPluginWithSubPlugins(): void {
		$this->mock_plugins( array(
			'multi-plugin/multi-plugin.php' => array(
				'Name'    => 'Multi Plugin',
				'Version' => '1.0.0',
			),
		));

		$cve_database = array(
			'multi-plugin' => array(
				array(
					'id'       => 'CVE-TEST-009',
					'severity' => 7.0,
					'fixed_in' => '1.5.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		$this->assertIsArray( $result );
		$this->assertEquals( 1, $result['meta']['vulnerable_plugins'] );
	}

	/**
	 * Test all severity levels map correctly.
	 *
	 * @return void
	 */
	public function testSeverityLevelMapping(): void {
		$test_cases = array(
			1 => 'high',  // Single vulnerability
			2 => 'high',  // Two vulnerabilities
			3 => 'critical', // Three or more
			5 => 'critical',
		);

		foreach ( $test_cases as $vuln_count => $expected_severity ) {
			$this->mock_plugins( array(
				"plugin{$vuln_count}/plugin{$vuln_count}.php" => array(
					'Name'    => "Plugin {$vuln_count}",
					'Version' => '1.0.0',
				),
			));

			$cve_database = array(
				"plugin{$vuln_count}" => array(),
			);

			// Add vulnerabilities
			for ( $i = 0; $i < $vuln_count; $i++ ) {
				$cve_database["plugin{$vuln_count}"][] = array(
					'id'       => "CVE-TEST-{$i}",
					'severity' => 7.0,
					'fixed_in' => '2.0.0',
				);
			}

			Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );
			$result = Diagnostic_Vulnerable_Plugin_Detection::check();

			$this->assertEquals(
				$expected_severity,
				$result['severity'],
				"Failed for vulnerability count: {$vuln_count}"
			);

			Diagnostic_Vulnerable_Plugin_Detection::clear_cve_cache();
		}
	}

	/**
	 * Test description contains translated strings.
	 *
	 * @return void
	 */
	public function testDescriptionHasTranslatableStrings(): void {
		$this->mock_plugins( array(
			'test-plugin/test-plugin.php' => array(
				'Name'    => 'Test Plugin',
				'Version' => '1.0.0',
			),
		));

		$cve_database = array(
			'test-plugin' => array(
				array(
					'id'       => 'CVE-TEST-010',
					'severity' => 8.0,
					'fixed_in' => '2.0.0',
				),
			),
		);

		Diagnostic_Vulnerable_Plugin_Detection::set_test_cve_database( $cve_database );

		$result = Diagnostic_Vulnerable_Plugin_Detection::check();

		// Description should contain numbers and context
		$this->assertStringContainsString( '1', $result['description'] );
		$this->assertNotEmpty( $result['description'] );
	}

	/**
	 * Helper: Mock plugins list.
	 *
	 * @param array $plugins Plugins to mock.
	 * @return void
	 */
	private function mock_plugins( $plugins ) {
		// Mock get_plugins function
		if ( ! function_exists( 'get_plugins' ) ) {
			function get_plugins( $plugin_folder = '' ) {
				return array();
			}
		}

		// Use filter to override plugins
		add_filter( 'option_active_plugins', function() use ( $plugins ) {
			return array_keys( $plugins );
		});

		// Override get_plugins via reflection or global
		$GLOBALS['_wpshadow_mock_plugins'] = $plugins;
	}
}
