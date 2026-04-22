<?php
/**
 * Service Meta Fields Test
 *
 * @package WPShadow
 */

namespace WPShadow\Tests;

/**
 * Test Service_Meta_Fields implementation.
 */
class ServiceMetaFieldsTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Test service meta fields class file exists.
	 *
	 * @return void
	 */
	public function test_meta_fields_class_file_exists(): void {
		$file = dirname( __DIR__ ) . '/includes/content/post-types/class-service-meta-fields.php';
		$this->assertFileExists( $file );
	}

	/**
	 * Test meta fields class has required methods.
	 *
	 * @return void
	 */
	public function test_meta_fields_has_required_methods(): void {
		$file    = dirname( __DIR__ ) . '/includes/content/post-types/class-service-meta-fields.php';
		$content = file_get_contents( $file );

		$this->assertStringContainsString( 'public static function init()', $content );
		$this->assertStringContainsString( 'public static function get_status(', $content );
		$this->assertStringContainsString( 'public static function set_status(', $content );
		$this->assertStringContainsString( 'public static function get_pricing(', $content );
		$this->assertStringContainsString( 'public static function set_pricing(', $content );
		$this->assertStringContainsString( 'public static function get_duration(', $content );
		$this->assertStringContainsString( 'public static function set_duration(', $content );
		$this->assertStringContainsString( 'public static function get_deliverables(', $content );
		$this->assertStringContainsString( 'public static function set_deliverables(', $content );
	}

	/**
	 * Test meta fields constants are defined.
	 *
	 * @return void
	 */
	public function test_meta_fields_constants_defined(): void {
		$file    = dirname( __DIR__ ) . '/includes/content/post-types/class-service-meta-fields.php';
		$content = file_get_contents( $file );

		$this->assertStringContainsString( 'const STATUS_ACTIVE', $content );
		$this->assertStringContainsString( 'const STATUS_COMING_SOON', $content );
		$this->assertStringContainsString( 'const STATUS_SEASONAL', $content );
		$this->assertStringContainsString( 'const STATUS_ENDED', $content );
	}

	/**
	 * Test meta fields class is valid PHP.
	 *
	 * @return void
	 */
	public function test_meta_fields_valid_php(): void {
		$file   = dirname( __DIR__ ) . '/includes/content/post-types/class-service-meta-fields.php';
		$result = shell_exec( "php -l " . escapeshellarg( $file ) . " 2>&1" );

		$this->assertStringContainsString( 'No syntax errors', $result );
	}

	/**
	 * Test Service CPT has comments support.
	 *
	 * @return void
	 */
	public function test_service_cpt_has_comments_support(): void {
		$file    = dirname( __DIR__ ) . '/includes/content/post-types/class-site-content-models.php';
		$content = file_get_contents( $file );

		// Check that 'service' array includes 'comments' in supports.
		$matches = array();
		$found   = preg_match( "/'service'\\s*=>\\s*array\\(.*?'supports'\\s*=>\\s*array\\(([^)]*)\\)/s", $content, $matches );

		$this->assertSame( 1, $found );
		$this->assertNotEmpty( $matches[1] );
		$this->assertStringContainsString( 'comments', $matches[1] );
	}

	/**
	 * Test meta fields integration in site-content-models.
	 *
	 * @return void
	 */
	public function test_meta_fields_required_in_site_content_models(): void {
		$file    = dirname( __DIR__ ) . '/includes/content/post-types/class-site-content-models.php';
		$content = file_get_contents( $file );

		$this->assertStringContainsString( "require_once __DIR__ . '/class-service-meta-fields.php'", $content );
		$this->assertStringContainsString( 'Service_Meta_Fields::init()', $content );
	}

	/**
	 * Test pricing schema methods added to Service_Schema_Output.
	 *
	 * @return void
	 */
	public function test_schema_output_includes_pricing_methods(): void {
		$file    = dirname( __DIR__ ) . '/includes/content/post-types/class-service-schema-output.php';
		$content = file_get_contents( $file );

		$this->assertStringContainsString( 'get_pricing_schema', $content );
		$this->assertStringContainsString( 'get_duration_schema', $content );
	}
}
