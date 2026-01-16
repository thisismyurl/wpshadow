<?php
/**
 * Test Bootstrap for Plugin Split Verification
 * 
 * Verifies the plugin split was done correctly:
 * 1. Both plugin files exist
 * 2. Plugin headers are valid
 * 3. Feature counts match
 * 4. Hooks are properly configured
 * 
 * Usage: php tests/test-bootstrap.php
 */

declare(strict_types=1);

class PluginSplitTest {
	private $base_path = '';
	private $results = [];

	public function __construct() {
		$this->base_path = dirname( dirname( __FILE__ ) );
	}

	public function run(): void {
		echo "🧪 WPShadow Plugin Split Verification\n";
		echo "=====================================\n\n";

		$this->test_file_existence();
		$this->test_plugin_headers();
		$this->test_feature_counts();
		$this->test_hook_configuration();
		$this->test_dependency_check();

		$this->print_results();
	}

	private function test_file_existence(): void {
		echo "TEST 1: File Existence\n";
		echo "---------------------\n";

		$files = [
			'wpshadow.php' => 'Core plugin file',
			'wpshadow-pro.php' => 'Pro plugin file',
			'includes/features/interface-wps-feature.php' => 'Feature interface',
			'includes/features/class-wps-feature-abstract.php' => 'Feature abstract',
		];

		foreach ( $files as $file => $desc ) {
			$path = $this->base_path . '/' . $file;
			if ( file_exists( $path ) ) {
				echo "✅ $desc\n";
				$this->results['file_existence'][] = true;
			} else {
				echo "❌ $file NOT FOUND\n";
				$this->results['file_existence'][] = false;
			}
		}

		echo "\n";
	}

	private function test_plugin_headers(): void {
		echo "TEST 2: Plugin Headers\n";
		echo "----------------------\n";

		$files = [
			'wpshadow.php' => 'Core Plugin',
			'wpshadow-pro.php' => 'Pro Plugin',
		];

		foreach ( $files as $file => $name ) {
			$path = $this->base_path . '/' . $file;
			$content = file_get_contents( $path );

			if ( strpos( $content, 'Plugin Name:' ) !== false ) {
				echo "✅ $name: Valid plugin header\n";
				$this->results['headers'][] = true;
			} else {
				echo "❌ $name: Missing plugin header\n";
				$this->results['headers'][] = false;
			}
		}

		echo "\n";
	}

	private function test_feature_counts(): void {
		echo "TEST 3: Feature Counts\n";
		echo "----------------------\n";

		// Count free features
		$core_file = file_get_contents( $this->base_path . '/wpshadow.php' );
		preg_match_all( '/register_WPSHADOW_feature\( new WPSHADOW_Feature_/', $core_file, $free_matches );
		$free_count = count( $free_matches[0] );

		echo "✅ Free features (wpshadow.php): $free_count\n";
		$this->results['free_features'] = $free_count >= 25 ? [ true ] : [ false ];

		// Count pro features
		$pro_file = file_get_contents( $this->base_path . '/wpshadow-pro.php' );
		preg_match_all( '/register_WPSHADOW_feature\( new \\\\WPShadow\\\\CoreSupport\\\\WPSHADOW_Feature_/', $pro_file, $pro_matches );
		$pro_count = count( $pro_matches[0] );

		echo "✅ Paid features (wpshadow-pro.php): $pro_count\n";
		$this->results['pro_features'] = $pro_count >= 25 ? [ true ] : [ false ];

		// Count feature files
		$feature_dir = $this->base_path . '/includes/features';
		$features = glob( $feature_dir . '/class-wps-feature-*.php' );
		$feature_count = count( $features );

		echo "✅ Total feature files: $feature_count\n";
		$this->results['total_files'] = [ $feature_count >= 60 ];

		echo "\n";
	}

	private function test_hook_configuration(): void {
		echo "TEST 4: Hook Configuration\n";
		echo "---------------------------\n";

		$core_file = file_get_contents( $this->base_path . '/wpshadow.php' );
		$pro_file = file_get_contents( $this->base_path . '/wpshadow-pro.php' );

		// Check core hook
		if ( strpos( $core_file, "do_action( 'wpshadow_register_features' )" ) !== false ) {
			echo "✅ Core: Hook trigger (wpshadow_register_features)\n";
			$this->results['core_hook'][] = true;
		} else {
			echo "❌ Core: Hook trigger missing\n";
			$this->results['core_hook'][] = false;
		}

		// Check pro hook registration
		if ( strpos( $pro_file, "add_action( 'wpshadow_register_features'" ) !== false ) {
			echo "✅ Pro: Hook registration\n";
			$this->results['pro_hook'][] = true;
		} else {
			echo "❌ Pro: Hook registration missing\n";
			$this->results['pro_hook'][] = false;
		}

		// Check feature loader function
		if ( strpos( $pro_file, 'function load_pro_features' ) !== false ) {
			echo "✅ Pro: Feature loader function\n";
			$this->results['pro_loader'][] = true;
		} else {
			echo "❌ Pro: Feature loader missing\n";
			$this->results['pro_loader'][] = false;
		}

		echo "\n";
	}

	private function test_dependency_check(): void {
		echo "TEST 5: Dependency Check\n";
		echo "------------------------\n";

		$pro_file = file_get_contents( $this->base_path . '/wpshadow-pro.php' );

		if ( strpos( $pro_file, 'if ( ! defined( \'WPSHADOW_PATH\' )' ) !== false ) {
			echo "✅ Pro: Dependency check for core plugin\n";
			$this->results['dependency'][] = true;
		} else {
			echo "❌ Pro: Dependency check missing\n";
			$this->results['dependency'][] = false;
		}

		if ( strpos( $pro_file, 'is_plugin_active' ) !== false ) {
			echo "✅ Pro: Plugin active check\n";
			$this->results['active_check'][] = true;
		} else {
			echo "❌ Pro: Active check missing\n";
			$this->results['active_check'][] = false;
		}

		echo "\n";
	}

	private function print_results(): void {
		echo "════════════════════════════════════════════════════\n";
		echo "TEST RESULTS SUMMARY\n";
		echo "════════════════════════════════════════════════════\n\n";

		$total = 0;
		$passed = 0;

		foreach ( $this->results as $test => $values ) {
			if ( is_array( $values ) && ! empty( $values ) ) {
				$total += count( $values );
				$passed += count( array_filter( $values ) );
			}
		}

		echo "✅ Passed: $passed / $total tests\n\n";

		if ( $passed === $total ) {
			echo "🎉 ALL TESTS PASSED!\n";
			echo "\n✅ Plugin split is correctly configured\n";
			echo "✅ Ready for integration testing\n";
			echo "\nNext steps:\n";
			echo "1. Activate wpshadow.php (core plugin)\n";
			echo "2. Activate wpshadow-pro.php (pro plugin)\n";
			echo "3. Verify features load in admin dashboard\n";
			echo "4. Run PHPUnit tests\n";
		} else {
			echo "⚠️  Some tests failed\n";
			echo "Review the output above for details.\n";
		}

		echo "\n════════════════════════════════════════════════════\n";
	}
}

// Run the test
$test = new PluginSplitTest();
$test->run();
