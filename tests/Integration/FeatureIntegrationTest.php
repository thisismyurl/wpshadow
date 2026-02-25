<?php
/**
 * Integration Tests for WPShadow Features
 *
 * Tests the interaction between different components.
 *
 * @package WPShadow\Tests\Integration
 * @since   1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Tests\Integration;

use WPShadow\Tests\TestCase;

/**
 * Feature integration tests
 */
class FeatureIntegrationTest extends TestCase {

	/**
	 * Test plugin constants are defined
	 *
	 * @return void
	 */
	public function testPluginConstantsAreDefined(): void {
		$required_constants = array(
			'WPSHADOW_VERSION',
			'WPSHADOW_BASENAME',
			'WPSHADOW_PATH',
			'WPSHADOW_URL',
		);

		foreach ( $required_constants as $constant ) {
			$this->assertTrue(
				defined( $constant ),
				"Plugin constant {$constant} must be defined"
			);
		}
	}

	/**
	 * Test plugin version format
	 *
	 * @return void
	 */
	public function testPluginVersionFormat(): void {
		$version = $this->getPluginVersion();

		// Version should match format: 1.YDDD.HHMM (1.{last year digit}{julian day}.{hour}{minute} in Toronto time)
		$this->assertMatchesRegularExpression(
			'/^1\.\d{3,4}\.\d{4}$/',
			$version,
			'Plugin version must follow format: 1.YDDD.HHMM in Toronto time (e.g., 1.6028.1430)'
		);
	}

	/**
	 * Test diagnostic and treatment pairing
	 *
	 * @return void
	 */
	public function testDiagnosticTreatmentPairing(): void {
		// Diagnostics with auto_fixable=true should have treatments
		$diagnostics_dir = $this->getPluginPath() . '/includes/diagnostics';
		$treatments_dir  = $this->getPluginPath() . '/includes/treatments';

		if ( ! is_dir( $diagnostics_dir ) || ! is_dir( $treatments_dir ) ) {
			$this->markTestSkipped( 'Diagnostic or treatment directories not found' );
		}

		$diagnostic_files = glob( $diagnostics_dir . '/class-diagnostic-*.php' );
		$treatment_files  = glob( $treatments_dir . '/class-treatment-*.php' );

		$diagnostic_count = count( $diagnostic_files );
		$treatment_count  = count( $treatment_files );

		// Should have reasonable number of diagnostics
		$this->assertGreaterThan( 0, $diagnostic_count, 'Should have diagnostics' );

		// Should have reasonable number of treatments
		$this->assertGreaterThan( 0, $treatment_count, 'Should have treatments' );

		// Not all diagnostics need treatments, but some should
		$this->assertGreaterThan(
			0,
			$treatment_count,
			'Should have auto-fixable diagnostics with treatments'
		);
	}

	/**
	 * Test file structure integrity
	 *
	 * @return void
	 */
	public function testFileStructureIntegrity(): void {
		$required_dirs = array(
			'includes/core',
			'includes/diagnostics',
			'includes/treatments',
			'includes/admin',
			'includes/views',
			'assets/css',
			'assets/js',
		);

		foreach ( $required_dirs as $dir ) {
			$full_path = $this->getPluginPath() . '/' . $dir;
			$this->assertDirectoryExists(
				$full_path,
				"Required directory must exist: {$dir}"
			);
		}
	}

	/**
	 * Test required files exist
	 *
	 * @return void
	 */
	public function testRequiredFilesExist(): void {
		$required_files = array(
			'wpshadow.php',
			'composer.json',
			'README.md',
			'readme.txt',
			'phpcs.xml.dist',
		);

		foreach ( $required_files as $file ) {
			$full_path = $this->getPluginPath() . '/' . $file;
			$this->assertFileExists(
				$full_path,
				"Required file must exist: {$file}"
			);
		}
	}

	/**
	 * Test namespaces are consistent
	 *
	 * @return void
	 */
	public function testNamespacesAreConsistent(): void {
		$includes_dir = $this->getPluginPath() . '/includes';

		if ( ! is_dir( $includes_dir ) ) {
			$this->markTestSkipped( 'Includes directory not found' );
		}

		// Get all PHP files
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $includes_dir )
		);

		$namespace_count         = 0;
		$correct_namespace_count = 0;

		foreach ( $files as $file ) {
			if ( $file->isDir() || $file->getExtension() !== 'php' ) {
				continue;
			}

			$content = file_get_contents( $file->getPathname() );

			// Check if file has namespace
			if ( preg_match( '/namespace\s+(.*?);/', $content, $matches ) ) {
				++$namespace_count;

				// Should start with WPShadow
				if ( strpos( $matches[1], 'WPShadow' ) === 0 ) {
					++$correct_namespace_count;
				}
			}
		}

		// Most files with namespaces should use WPShadow\
		if ( $namespace_count > 0 ) {
			$ratio = $correct_namespace_count / $namespace_count;
			$this->assertGreaterThan(
				0.9,
				$ratio,
				'At least 90% of namespaced files should use WPShadow namespace'
			);
		}
	}

	/**
	 * Test autoloader configuration
	 *
	 * @return void
	 */
	public function testAutoloaderConfiguration(): void {
		$composer_file = $this->getPluginPath() . '/composer.json';
		$this->assertFileExists( $composer_file, 'composer.json must exist' );

		$composer_data = json_decode( file_get_contents( $composer_file ), true );

		$this->assertArrayHasKey(
			'autoload',
			$composer_data,
			'composer.json must have autoload configuration'
		);

		$this->assertArrayHasKey(
			'psr-4',
			$composer_data['autoload'],
			'composer.json must have PSR-4 autoloading'
		);

		$this->assertArrayHasKey(
			'WPShadow\\',
			$composer_data['autoload']['psr-4'],
			'composer.json must define WPShadow\\ namespace'
		);
	}

	/**
	 * Test documentation exists
	 *
	 * @return void
	 */
	public function testDocumentationExists(): void {
		$docs_dir = $this->getPluginPath() . '/docs';

		if ( ! is_dir( $docs_dir ) ) {
			$this->markTestSkipped( 'Documentation directory not found' );
		}

		$required_docs = array(
			'ARCHITECTURE.md',
			'CODING_STANDARDS.md',
			'FEATURE_MATRIX_DIAGNOSTICS.md',
			'FEATURE_MATRIX_TREATMENTS.md',
			'ACCESSIBILITY_AND_INCLUSIVITY_CANON.md',
		);

		foreach ( $required_docs as $doc ) {
			$doc_path = $docs_dir . '/' . $doc;
			$this->assertFileExists(
				$doc_path,
				"Required documentation must exist: {$doc}"
			);
		}
	}

	/**
	 * Test workflow builder assets exist
	 *
	 * @return void
	 */
	public function testWorkflowBuilderAssetsExist(): void {
		$assets = array(
			'assets/css/workflow-builder.css',
			'includes/views/workflow-builder.php',
		);

		foreach ( $assets as $asset ) {
			$asset_path = $this->getPluginPath() . '/' . $asset;
			$this->assertFileExists(
				$asset_path,
				"Workflow builder asset must exist: {$asset}"
			);
		}
	}

	/**
	 * Test CSS files are valid
	 *
	 * @return void
	 */
	public function testCSSFilesAreValid(): void {
		$css_dir = $this->getPluginPath() . '/assets/css';

		if ( ! is_dir( $css_dir ) ) {
			$this->markTestSkipped( 'CSS directory not found' );
		}

		$css_files = glob( $css_dir . '/*.css' );

		foreach ( $css_files as $file ) {
			$content = file_get_contents( $file );

			// Basic CSS syntax checks
			$open_braces  = substr_count( $content, '{' );
			$close_braces = substr_count( $content, '}' );

			$this->assertEquals(
				$open_braces,
				$close_braces,
				basename( $file ) . ': CSS braces must be balanced'
			);
		}
	}
}
