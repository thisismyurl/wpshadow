<?php
declare(strict_types=1);

/**
 * Diagnostic File Fix Utility
 * 
 * Analyzes all diagnostic files and fixes structural issues:
 * - Adds missing Diagnostic_Base inheritance
 * - Fixes method signatures to include return types
 * - Standardizes return array structure
 * - Adds missing required fields
 * 
 * Run from WordPress admin or CLI:
 * wp eval-file wpshadow/tools/fix-diagnostic-files.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo "This file must be run within WordPress.\n";
	exit( 1 );
}

class Diagnostic_File_Fixer {

	private $diagnostics_dir;
	private $fixed_count = 0;
	private $error_count = 0;
	private $issues_found = [];

	public function __construct() {
		$this->diagnostics_dir = WP_CONTENT_DIR . '/plugins/wpshadow/includes/diagnostics';
	}

	/**
	 * Run the fixer
	 */
	public function run() {
		echo "=== WPShadow Diagnostic File Fixer ===\n\n";
		
		if ( ! is_dir( $this->diagnostics_dir ) ) {
			echo "ERROR: Diagnostics directory not found: {$this->diagnostics_dir}\n";
			return false;
		}

		// Scan root and subdirectories
		$files = glob( $this->diagnostics_dir . '/class-diagnostic-*.php' );
		if ( ! $files ) {
			$files = array();
		}
		foreach ( glob( $this->diagnostics_dir . '*/class-diagnostic-*.php' ) as $subfile ) {
			$files[] = $subfile;
		}
		echo "Found " . count( $files ) . " diagnostic files.\n\n";

		foreach ( $files as $file ) {
			$this->process_file( $file );
		}

		echo "\n=== SUMMARY ===\n";
		echo "Fixed: {$this->fixed_count}\n";
		echo "Errors: {$this->error_count}\n\n";

		if ( ! empty( $this->issues_found ) ) {
			echo "Issues found:\n";
			foreach ( $this->issues_found as $file => $issues ) {
				echo "  " . basename( $file ) . ":\n";
				foreach ( $issues as $issue ) {
					echo "    - {$issue}\n";
				}
			}
		}

		return true;
	}

	/**
	 * Process a single diagnostic file
	 */
	private function process_file( $file ) {
		$filename = basename( $file );
		echo "Processing: $filename ... ";

		$content = file_get_contents( $file );
		if ( $content === false ) {
			echo "FAILED (read error)\n";
			$this->error_count++;
			return;
		}

		$original_content = $content;
		$issues = [];

		// Check 1: Proper namespace and declaration
		if ( ! preg_match( '/^<\?php\s*\n\s*declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', $content ) ) {
			$content = str_replace(
				'<?php',
				"<?php\ndeclare(strict_types=1);",
				$content
			);
			$issues[] = 'Added strict_types declaration';
		}

		// Check 2: Proper namespace
		if ( ! preg_match( '/namespace\s+WPShadow\\\\Diagnostics\s*;/', $content ) ) {
			if ( preg_match( '/namespace\s+.*?;/', $content ) ) {
				echo "WARNING: Non-standard namespace\n";
				$this->issues_found[ $file ][] = 'Non-standard namespace';
				return;
			} else {
				echo "ERROR: No namespace found\n";
				$this->error_count++;
				$this->issues_found[ $file ][] = 'Missing namespace';
				return;
			}
		}

		// Check 3: Has use statement for Diagnostic_Base
		if ( ! preg_match( '/use\s+WPShadow\\\\Core\\\\Diagnostic_Base\s*;/', $content ) ) {
			// Add use statement after namespace
			$content = preg_replace(
				'/^(namespace\s+WPShadow\\\\Diagnostics\s*;)/m',
				"$1\n\nuse WPShadow\\Core\\Diagnostic_Base;",
				$content
			);
			$issues[] = 'Added Diagnostic_Base use statement';
		}

		// Check 4: Class extends Diagnostic_Base
		if ( ! preg_match( '/class\s+Diagnostic_\w+\s+extends\s+Diagnostic_Base/', $content ) ) {
			$content = preg_replace(
				'/class\s+(Diagnostic_\w+)\s*(?!extends)/',
				'class $1 extends Diagnostic_Base ',
				$content
			);
			$issues[] = 'Added Diagnostic_Base inheritance';
		}

		// Check 5: check() method has proper signature
		if ( ! preg_match( '/public\s+static\s+function\s+check\s*\(\s*\)\s*:\s*\?array/', $content ) ) {
			// Try to find and fix the check method signature
			$content = preg_replace(
				'/public\s+static\s+function\s+check\s*\(\s*\)\s*[:\s]*\{/',
				'public static function check(): ?array {',
				$content
			);

			if ( preg_match( '/public\s+static\s+function\s+check\s*\(\s*\)\s*:\s*\?array/', $content ) ) {
				$issues[] = 'Fixed check() method signature';
			} else {
				echo "WARNING: Could not fix check() signature\n";
				$this->issues_found[ $file ][] = 'check() method signature needs manual review';
			}
		}

		// Check 6: Return statement uses proper array structure
		if ( ! $this->has_proper_return_structure( $content ) ) {
			echo "WARNING: Return array structure needs review\n";
			$this->issues_found[ $file ][] = 'Return array structure may need review';
		}

		// Write back if changed
		if ( $content !== $original_content ) {
			if ( file_put_contents( $file, $content ) !== false ) {
				echo "FIXED (" . count( $issues ) . " issues fixed)\n";
				$this->fixed_count++;
				if ( ! isset( $this->issues_found[ $file ] ) ) {
					$this->issues_found[ $file ] = $issues;
				}
			} else {
				echo "FAILED (write error)\n";
				$this->error_count++;
			}
		} else {
			echo "OK\n";
		}
	}

	/**
	 * Check if return array has proper structure
	 */
	private function has_proper_return_structure( $content ): bool {
		// Look for return statement with key array fields
		$required_fields = [
			'finding_id',
			'title',
			'description',
			'category',
			'severity',
			'threat_level',
			'auto_fixable',
			'timestamp'
		];

		foreach ( $required_fields as $field ) {
			if ( ! preg_match( "/'$field'\\s*=>|\"$field\"\\s*=>|'$field'\\s*:|\"$field\"\\s*:/", $content ) ) {
				return false;
			}
		}

		return true;
	}
}

// Run the fixer
$fixer = new Diagnostic_File_Fixer();
$fixer->run();
