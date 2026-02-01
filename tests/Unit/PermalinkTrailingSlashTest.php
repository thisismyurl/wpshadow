<?php
/**
 * Tests for Permalink Trailing Slash Diagnostic
 *
 * @package WPShadow\Tests\Unit
 * @since   1.26032.0903
 */

declare(strict_types=1);

namespace WPShadow\Tests\Unit;

// Manually load the diagnostic class
require_once __DIR__ . '/../../includes/diagnostics/tests/seo/class-diagnostic-permalink-trailing-slash.php';

use WPShadow\Diagnostics\Diagnostic_Permalink_Trailing_Slash;
use WPShadow\Tests\TestCase;

/**
 * Permalink Trailing Slash Diagnostic tests
 */
class PermalinkTrailingSlashTest extends TestCase {

	/**
	 * Mock WP_Rewrite object
	 *
	 * @var object
	 */
	private $mock_wp_rewrite;

	/**
	 * Setup before each test
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		// Mock global $wp_rewrite
		global $wp_rewrite;
		$this->mock_wp_rewrite = new \stdClass();
		$this->mock_wp_rewrite->permalink_structure = '';
		$wp_rewrite = $this->mock_wp_rewrite;

		// Mock WordPress functions
		if ( ! function_exists( 'get_option' ) ) {
			function get_option( $option, $default = false ) {
				global $wp_options_mock;
				return $wp_options_mock[ $option ] ?? $default;
			}
		}

		if ( ! function_exists( '__' ) ) {
			function __( $text, $domain = 'default' ) {
				return $text;
			}
		}

		if ( ! function_exists( 'sprintf' ) && ! function_exists( 'sprintf' ) ) {
			// sprintf is a PHP built-in, so this is just a safeguard
		}

		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', '/tmp/' );
		}

		// Initialize options mock
		global $wp_options_mock;
		$wp_options_mock = array();
	}

	/**
	 * Cleanup after each test
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		global $wp_rewrite, $wp_options_mock;
		$wp_rewrite = null;
		$wp_options_mock = array();
		parent::tearDown();
	}

	/**
	 * Test diagnostic returns null when not using pretty permalinks
	 *
	 * @return void
	 */
	public function testReturnsNullWhenNotUsingPrettyPermalinks(): void {
		global $wp_rewrite, $wp_options_mock;
		
		$wp_rewrite = null;
		$wp_options_mock['permalink_structure'] = '';

		$result = Diagnostic_Permalink_Trailing_Slash::check();
		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic returns null with consistent trailing slash configuration
	 *
	 * @return void
	 */
	public function testReturnsNullWithConsistentTrailingSlash(): void {
		global $wp_rewrite, $wp_options_mock;

		// Setup pretty permalinks with trailing slash
		$wp_rewrite = new \stdClass();
		$wp_rewrite->permalink_structure = '/%year%/%monthnum%/%postname%/';
		$wp_options_mock['permalink_structure'] = '/%year%/%monthnum%/%postname%/';
		$wp_options_mock['category_base'] = '';
		$wp_options_mock['tag_base'] = '';

		// Mock using_permalinks method
		$wp_rewrite->using_permalinks = function() {
			return true;
		};

		// Create a mockable version
		$mock_rewrite = new class {
			public $permalink_structure = '/%year%/%monthnum%/%postname%/';
			public function using_permalinks() {
				return true;
			}
		};
		$wp_rewrite = $mock_rewrite;

		$result = Diagnostic_Permalink_Trailing_Slash::check();
		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic detects postname without trailing slash
	 *
	 * @return void
	 */
	public function testDetectsPostnameWithoutTrailingSlash(): void {
		global $wp_rewrite, $wp_options_mock;

		$mock_rewrite = new class {
			public $permalink_structure = '/%postname%';
			public function using_permalinks() {
				return true;
			}
		};
		$wp_rewrite = $mock_rewrite;

		$wp_options_mock['permalink_structure'] = '/%postname%';
		$wp_options_mock['category_base'] = '';
		$wp_options_mock['tag_base'] = '';

		$result = Diagnostic_Permalink_Trailing_Slash::check();
		
		$this->assertIsArray( $result );
		$this->assertValidFinding( $result );
		$this->assertEquals( 'permalink-trailing-slash', $result['id'] );
		$this->assertEquals( 60, $result['threat_level'] );
		$this->assertEquals( 'medium', $result['severity'] );
	}

	/**
	 * Test diagnostic detects category base mismatch
	 *
	 * @return void
	 */
	public function testDetectsCategoryBaseMismatch(): void {
		global $wp_rewrite, $wp_options_mock;

		$mock_rewrite = new class {
			public $permalink_structure = '/%postname%/';
			public function using_permalinks() {
				return true;
			}
		};
		$wp_rewrite = $mock_rewrite;

		$wp_options_mock['permalink_structure'] = '/%postname%/';
		$wp_options_mock['category_base'] = 'topics'; // No trailing slash
		$wp_options_mock['tag_base'] = '';

		$result = Diagnostic_Permalink_Trailing_Slash::check();
		
		$this->assertIsArray( $result );
		$this->assertValidFinding( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'issue_type', $result['details'] );
		$this->assertEquals( 'category_base_mismatch', $result['details']['issue_type'] );
	}

	/**
	 * Test diagnostic detects tag base mismatch
	 *
	 * @return void
	 */
	public function testDetectsTagBaseMismatch(): void {
		global $wp_rewrite, $wp_options_mock;

		$mock_rewrite = new class {
			public $permalink_structure = '/%postname%/';
			public function using_permalinks() {
				return true;
			}
		};
		$wp_rewrite = $mock_rewrite;

		$wp_options_mock['permalink_structure'] = '/%postname%/';
		$wp_options_mock['category_base'] = '';
		$wp_options_mock['tag_base'] = 'keywords'; // No trailing slash

		$result = Diagnostic_Permalink_Trailing_Slash::check();
		
		$this->assertIsArray( $result );
		$this->assertValidFinding( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertEquals( 'tag_base_mismatch', $result['details']['issue_type'] );
	}

	/**
	 * Test diagnostic provides detailed information in findings
	 *
	 * @return void
	 */
	public function testProvidesDetailedFindingInformation(): void {
		global $wp_rewrite, $wp_options_mock;

		$mock_rewrite = new class {
			public $permalink_structure = '/%postname%';
			public function using_permalinks() {
				return true;
			}
		};
		$wp_rewrite = $mock_rewrite;

		$wp_options_mock['permalink_structure'] = '/%postname%';
		$wp_options_mock['category_base'] = '';
		$wp_options_mock['tag_base'] = '';

		$result = Diagnostic_Permalink_Trailing_Slash::check();
		
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'details', $result );
		$this->assertArrayHasKey( 'permalink_structure', $result['details'] );
		$this->assertArrayHasKey( 'has_trailing_slash', $result['details'] );
		$this->assertArrayHasKey( 'category_base', $result['details'] );
		$this->assertArrayHasKey( 'tag_base', $result['details'] );
		$this->assertArrayHasKey( 'issue_type', $result['details'] );
		$this->assertArrayHasKey( 'recommendation', $result['details'] );
	}

	/**
	 * Test diagnostic with year/month/postname structure with trailing slash
	 *
	 * @return void
	 */
	public function testYearMonthPostnameWithTrailingSlash(): void {
		global $wp_rewrite, $wp_options_mock;

		$mock_rewrite = new class {
			public $permalink_structure = '/%year%/%monthnum%/%postname%/';
			public function using_permalinks() {
				return true;
			}
		};
		$wp_rewrite = $mock_rewrite;

		$wp_options_mock['permalink_structure'] = '/%year%/%monthnum%/%postname%/';
		$wp_options_mock['category_base'] = '';
		$wp_options_mock['tag_base'] = '';

		$result = Diagnostic_Permalink_Trailing_Slash::check();
		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic handles empty category/tag bases
	 *
	 * @return void
	 */
	public function testHandlesEmptyCategoryTagBases(): void {
		global $wp_rewrite, $wp_options_mock;

		$mock_rewrite = new class {
			public $permalink_structure = '/%postname%/';
			public function using_permalinks() {
				return true;
			}
		};
		$wp_rewrite = $mock_rewrite;

		$wp_options_mock['permalink_structure'] = '/%postname%/';
		$wp_options_mock['category_base'] = '';
		$wp_options_mock['tag_base'] = '';

		$result = Diagnostic_Permalink_Trailing_Slash::check();
		
		// Should not detect issues with empty bases
		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic metadata
	 *
	 * @return void
	 */
	public function testDiagnosticMetadata(): void {
		$this->assertEquals( 'permalink-trailing-slash', Diagnostic_Permalink_Trailing_Slash::get_slug() );
		$this->assertEquals( 'Permalink Trailing Slash', Diagnostic_Permalink_Trailing_Slash::get_title() );
		$this->assertEquals( 'Tests trailing slash consistency and detects redirect loops', Diagnostic_Permalink_Trailing_Slash::get_description() );
		$this->assertEquals( 'seo', Diagnostic_Permalink_Trailing_Slash::get_family() );
	}

	/**
	 * Test diagnostic returns valid finding structure
	 *
	 * @return void
	 */
	public function testReturnsValidFindingStructure(): void {
		global $wp_rewrite, $wp_options_mock;

		$mock_rewrite = new class {
			public $permalink_structure = '/%postname%';
			public function using_permalinks() {
				return true;
			}
		};
		$wp_rewrite = $mock_rewrite;

		$wp_options_mock['permalink_structure'] = '/%postname%';
		$wp_options_mock['category_base'] = '';
		$wp_options_mock['tag_base'] = '';

		$result = Diagnostic_Permalink_Trailing_Slash::check();
		
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'id', $result );
		$this->assertArrayHasKey( 'title', $result );
		$this->assertArrayHasKey( 'description', $result );
		$this->assertArrayHasKey( 'severity', $result );
		$this->assertArrayHasKey( 'threat_level', $result );
		$this->assertArrayHasKey( 'auto_fixable', $result );
		$this->assertArrayHasKey( 'kb_link', $result );
		
		$this->assertEquals( 'permalink-trailing-slash', $result['id'] );
		$this->assertEquals( 60, $result['threat_level'] );
		$this->assertEquals( 'medium', $result['severity'] );
		$this->assertFalse( $result['auto_fixable'] );
	}

	/**
	 * Test diagnostic with custom category base with trailing slash
	 *
	 * @return void
	 */
	public function testCustomCategoryBaseWithTrailingSlash(): void {
		global $wp_rewrite, $wp_options_mock;

		$mock_rewrite = new class {
			public $permalink_structure = '/%postname%/';
			public function using_permalinks() {
				return true;
			}
		};
		$wp_rewrite = $mock_rewrite;

		$wp_options_mock['permalink_structure'] = '/%postname%/';
		$wp_options_mock['category_base'] = 'topics/'; // With trailing slash
		$wp_options_mock['tag_base'] = '';

		$result = Diagnostic_Permalink_Trailing_Slash::check();
		
		// Should not detect issues when consistent
		$this->assertNull( $result );
	}

	/**
	 * Test diagnostic is applicable
	 *
	 * @return void
	 */
	public function testDiagnosticIsApplicable(): void {
		$this->assertTrue( Diagnostic_Permalink_Trailing_Slash::is_applicable() );
	}
}
