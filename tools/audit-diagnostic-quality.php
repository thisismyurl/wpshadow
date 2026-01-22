<?php
declare(strict_types=1);

/**
 * Diagnostic Quality Auditor
 * 
 * Verifies all diagnostic files meet quality standards:
 * - Proper inheritance from Diagnostic_Base
 * - Required fields in return arrays
 * - Correct method signatures
 * - Namespace consistency
 */

class Diagnostic_Quality_Auditor {

	private $diagnostics_dir;
	private $valid_count = 0;
	private $warnings_count = 0;
	private $error_count = 0;
	private $issues = [];

	public function __construct() {
		$this->diagnostics_dir = __DIR__ . '/../includes/diagnostics';
	}

	/**
	 * Run the audit
	 */
	public function audit(): bool {
		echo "\n=== WPShadow Diagnostic Quality Audit ===\n\n";

		if ( ! is_dir( $this->diagnostics_dir ) ) {
			echo "ERROR: Directory not found\n";
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
		$total = count( $files );

		echo "Auditing {$total} files...\n\n";

		foreach ( $files as $file ) {
			$this->audit_file( $file );
		}

		$this->print_summary( $total );
		return $this->error_count === 0;
	}

	/**
	 * Audit a single file
	 */
	private function audit_file( $file ): void {
		$content = file_get_contents( $file );
		$filename = basename( $file );
		$file_issues = [];

		// Check 1: Proper PHP opening
		if ( ! preg_match( '/^<\?php/', $content ) ) {
			$file_issues[] = 'Missing PHP opening tag';
			$this->error_count++;
		}

		// Check 2: Strict types
		if ( ! preg_match( '/declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/', $content ) ) {
			$file_issues[] = 'Missing strict_types declaration';
			$this->warnings_count++;
		}

		// Check 3: Proper namespace
		if ( ! preg_match( '/namespace\s+WPShadow\\\\Diagnostics\s*;/', $content ) ) {
			$file_issues[] = 'Incorrect namespace';
			$this->error_count++;
		}

		// Check 4: Use statement for Diagnostic_Base
		if ( ! preg_match( '/use\s+WPShadow\\\\Core\\\\Diagnostic_Base/', $content ) ) {
			$file_issues[] = 'Missing Diagnostic_Base use statement';
			$this->warnings_count++;
		}

		// Check 5: Class extends Diagnostic_Base
		if ( ! preg_match( '/class\s+Diagnostic_\w+\s+extends\s+Diagnostic_Base/', $content ) ) {
			$file_issues[] = 'Class does not extend Diagnostic_Base';
			$this->error_count++;
		}

		// Check 6: check() method signature
		if ( ! preg_match( '/public\s+static\s+function\s+check\s*\(\s*\)\s*:\s*\?array/', $content ) ) {
			$file_issues[] = 'Incorrect check() method signature';
			$this->error_count++;
		}

		// Check 7: Return statement exists
		if ( ! preg_match( '/return\s+(?:array\s*\(|\[)/', $content ) ) {
			$file_issues[] = 'Missing return statement';
			$this->error_count++;
		}

		// Check 8: Has some key fields (either old 'id' or new 'finding_id')
		$has_id = preg_match( "/'(?:id|finding_id)'\\s*=>|\"(?:id|finding_id)\"\\s*=>|'(?:id|finding_id)'\\s*:|\"(?:id|finding_id)\"\\s*:/", $content );
		$has_title = preg_match( "/'title'\\s*=>|\"title\"\\s*=>|'title'\\s*:|\"title\"\\s*:/", $content );
		$has_severity = preg_match( "/'severity'\\s*=>|\"severity\"\\s*=>|'severity'\\s*:|\"severity\"\\s*:/", $content );

		if ( ! $has_id || ! $has_title || ! $has_severity ) {
			$missing = [];
			if ( ! $has_id ) {
				$missing[] = 'id/finding_id';
			}
			if ( ! $has_title ) {
				$missing[] = 'title';
			}
			if ( ! $has_severity ) {
				$missing[] = 'severity';
			}
			$file_issues[] = 'Missing key return fields: ' . implode( ', ', $missing );
			$this->warnings_count++;
		}

		// Check 9: No duplicate extends
		if ( preg_match( '/extends\s+Diagnostic_Base\s+extends/', $content ) ) {
			$file_issues[] = 'Duplicate extends clause';
			$this->error_count++;
		}

		// Report
		if ( empty( $file_issues ) ) {
			echo "✓ $filename\n";
			$this->valid_count++;
		} else {
			if ( $this->count_issues_by_severity( $file_issues ) > 0 ) {
				echo "✗ $filename\n";
				foreach ( $file_issues as $issue ) {
					echo "  ⚠ $issue\n";
				}
			}
		}

		if ( ! empty( $file_issues ) ) {
			$this->issues[ $filename ] = $file_issues;
		}
	}

	/**
	 * Count severe issues
	 */
	private function count_issues_by_severity( $issues ): int {
		$severe_keywords = [ 'extends', 'namespace', 'signature', 'Missing return', 'Duplicate' ];
		$count = 0;

		foreach ( $issues as $issue ) {
			foreach ( $severe_keywords as $keyword ) {
				if ( strpos( $issue, $keyword ) !== false ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Print audit summary
	 */
	private function print_summary( $total ): void {
		echo "\n=== AUDIT SUMMARY ===\n";
		echo "Total Files:   {$total}\n";
		echo "Valid:         {$this->valid_count} ✓\n";
		echo "Warnings:      {$this->warnings_count} ⚠\n";
		echo "Errors:        {$this->error_count} ✗\n";
		echo "Pass Rate:     " . round( ( $this->valid_count / $total ) * 100, 1 ) . "%\n";

		if ( ! empty( $this->issues ) ) {
			echo "\n=== ISSUES FOUND ===\n";
			echo count( $this->issues ) . " files with issues:\n\n";

			foreach ( array_slice( $this->issues, 0, 10 ) as $file => $issues ) {
				echo "  {$file}:\n";
				foreach ( $issues as $issue ) {
					echo "    - {$issue}\n";
				}
			}

			if ( count( $this->issues ) > 10 ) {
				echo "\n  ... and " . ( count( $this->issues ) - 10 ) . " more files\n";
			}
		}

		echo "\n";

		if ( $this->error_count === 0 ) {
			echo "✓ All files pass quality audit!\n";
		} else {
			echo "✗ {$this->error_count} critical issues found. Review above.\n";
		}
	}
}

// Run audit
if ( php_sapi_name() === 'cli' || ! function_exists( 'add_action' ) ) {
	$auditor = new Diagnostic_Quality_Auditor();
	$result = $auditor->audit();
	exit( $result ? 0 : 1 );
} else {
	echo "This script is designed to run from CLI.\n";
	echo "Usage: php tools/audit-diagnostic-quality.php\n";
}
