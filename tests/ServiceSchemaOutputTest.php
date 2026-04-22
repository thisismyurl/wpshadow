<?php
/**
 * Service Schema Output Test
 *
 * @package WPShadow
 */

namespace WPShadow\Tests;

/**
 * Test Service JSON-LD schema implementation.
 */
class ServiceSchemaOutputTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Test schema class file exists and is readable.
	 *
	 * @return void
	 */
	public function test_schema_class_file_exists(): void {
		$file = dirname( __DIR__ ) . '/includes/content/post-types/class-service-schema-output.php';
		$this->assertFileExists( $file );
		$this->assertFileIsReadable( $file );
	}

	/**
	 * Test schema class file contains required methods.
	 *
	 * @return void
	 */
	public function test_schema_class_has_required_methods(): void {
		$file    = dirname( __DIR__ ) . '/includes/content/post-types/class-service-schema-output.php';
		$content = file_get_contents( $file );

		$this->assertStringContainsString( 'public static function init()', $content );
		$this->assertStringContainsString( 'public static function output_service_schema()', $content );
		$this->assertStringContainsString( 'private static function build_service_schema(', $content );
	}

	/**
	 * Test schema class is valid PHP.
	 *
	 * @return void
	 */
	public function test_schema_class_valid_php(): void {
		$file   = dirname( __DIR__ ) . '/includes/content/post-types/class-service-schema-output.php';
		$result = shell_exec( "php -l " . escapeshellarg( $file ) . " 2>&1" );

		$this->assertStringContainsString( 'No syntax errors', $result );
	}

	/**
	 * Test schema integration in site-content-models.
	 *
	 * @return void
	 */
	public function test_schema_required_in_site_content_models(): void {
		$file    = dirname( __DIR__ ) . '/includes/content/post-types/class-site-content-models.php';
		$content = file_get_contents( $file );

		$this->assertStringContainsString( "require_once __DIR__ . '/class-service-schema-output.php'", $content );
		$this->assertStringContainsString( 'Service_Schema_Output::init()', $content );
	}

	/**
	 * Test service_category taxonomy is defined.
	 *
	 * @return void
	 */
	public function test_service_category_taxonomy_defined(): void {
		$file    = dirname( __DIR__ ) . '/includes/content/post-types/class-site-content-models.php';
		$content = file_get_contents( $file );

		$this->assertStringContainsString( "'service_category'", $content );
		$this->assertStringContainsString( "'Service Categories'", $content );
		$this->assertStringContainsString( "'object_types'  => array( 'service' )", $content );
	}
}

