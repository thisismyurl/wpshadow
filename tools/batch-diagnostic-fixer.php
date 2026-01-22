<?php
declare(strict_types=1);

/**
 * Batch Diagnostic File Fixer
 * 
 * Systematically fixes all 2500+ diagnostic stub files
 * to be consistent and error-free
 */

class Batch_Diagnostic_Fixer {

	private $diagnostics_dir;
	private $fixed = 0;
	private $skipped = 0;
	private $errors = 0;

	public function __construct() {
		$this->diagnostics_dir = __DIR__ . '/../includes/diagnostics';
	}

	/**
	 * Main entry point
	 */
	public function fix_all() {
		echo "\n=== WPShadow Diagnostic Batch Fixer ===\n";
		echo "Target: {$this->diagnostics_dir}\n\n";

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
		
		echo "Found {$total} diagnostic files.\n";
		echo "Processing...\n\n";

		foreach ( $files as $index => $file ) {
			$current = $index + 1;
			
			// Show progress every 100 files
			if ( $current % 100 === 0 ) {
				echo "[{$current}/{$total}] ";
			}

			if ( ! $this->fix_file( $file ) ) {
				echo "E";
				$this->errors++;
			} else {
				echo ".";
				$this->fixed++;
			}

			// New line every 60 dots
			if ( $current % 60 === 0 ) {
				echo " {$current}/{$total}\n";
			}
		}

		echo "\n\n=== COMPLETE ===\n";
		echo "Fixed:   {$this->fixed}\n";
		echo "Errors:  {$this->errors}\n";
		echo "Skipped: {$this->skipped}\n";
		echo "Total:   {$total}\n";

		return true;
	}

	/**
	 * Fix a single diagnostic file
	 */
	private function fix_file( $file ): bool {
		$content = @file_get_contents( $file );
		if ( $content === false ) {
			return false;
		}

		$original = $content;

		// Step 1: Ensure strict_types declaration
		if ( ! preg_match( '/^<\?php\s*\n\s*declare\s*\(\s*strict_types\s*=\s*1\s*\)\s*;/m', $content ) ) {
			// Remove opening tag and add proper opening
			$content = preg_replace( '/^<\?php\s*/m', "<?php\ndeclare(strict_types=1);\n", $content, 1 );
		}

		// Step 2: Extract class name
		if ( ! preg_match( '/class\s+(Diagnostic_\w+)/', $content, $matches ) ) {
			return false; // Not a valid diagnostic file
		}
		$class_name = $matches[1];

		// Step 3: Ensure namespace
		if ( ! preg_match( '/namespace\s+WPShadow\\\\Diagnostics/', $content ) ) {
			// Add namespace after strict_types
			$content = preg_replace(
				'/^(.*?declare.*?\n\n)/s',
				"$1namespace WPShadow\\Diagnostics;\n\n",
				$content,
				1
			);
		}

		// Step 4: Ensure use statement for Diagnostic_Base
		if ( ! preg_match( '/use\s+WPShadow\\\\Core\\\\Diagnostic_Base/', $content ) ) {
			// Add after namespace
			$content = preg_replace(
				'/^(namespace\s+WPShadow\\\\Diagnostics;)/m',
				"$1\n\nuse WPShadow\\Core\\Diagnostic_Base;",
				$content,
				1
			);
		}

		// Step 5: Fix class declaration
		$content = preg_replace(
			"/class\s+{$class_name}\s*(?!extends)/",
			"class {$class_name} extends Diagnostic_Base ",
			$content
		);

		// Step 6: Fix check() method signature
		$content = preg_replace(
			'/public\s+static\s+function\s+check\s*\(\s*\)\s*(?::\s*\?array\s*)?\{/',
			'public static function check(): ?array {',
			$content
		);

		// Step 7: Normalize return arrays
		$content = $this->normalize_return_arrays( $content, $class_name );

		// Step 8: Clean up whitespace
		$content = preg_replace( '/\n\n\n+/', "\n\n", $content );
		$content = trim( $content ) . "\n";

		// Write back if changed
		if ( $content !== $original ) {
			return @file_put_contents( $file, $content ) !== false;
		}

		$this->skipped++;
		return true;
	}

	/**
	 * Normalize return arrays in check() method
	 */
	private function normalize_return_arrays( $content, $class_name ): string {
		// Extract the check method body
		if ( ! preg_match( '/public\s+static\s+function\s+check\s*\(\s*\)\s*:\s*\?array\s*\{(.*?)(?=\s*public\s+|private\s+|protected\s+|^}/s)', $content, $matches ) ) {
			return $content;
		}

		$method_body = $matches[1];
		$original_body = $method_body;

		// Extract slug from class name
		$slug = $this->class_name_to_slug( $class_name );

		// Find all return statements
		if ( preg_match( '/return\s+\[(.*?)\];/s', $method_body, $array_matches ) ) {
			$array_content = $array_matches[1];
			
			// Parse key-value pairs
			$return_data = $this->parse_array_content( $array_content, $slug );
			
			// Format the return array properly
			$formatted_return = $this->format_return_array( $return_data );
			
			// Replace in content
			$method_body = preg_replace(
				'/return\s+\[.*?\];/s',
				"return {$formatted_return};",
				$method_body
			);
		}

		// Replace the method body in the content
		$content = str_replace( $original_body, $method_body, $content );

		return $content;
	}

	/**
	 * Convert class name to slug
	 */
	private function class_name_to_slug( $class_name ): string {
		// Remove "Diagnostic_" prefix
		$slug = preg_replace( '/^Diagnostic_/', '', $class_name );
		
		// Convert CamelCase to kebab-case
		$slug = preg_replace( '/([a-z])([A-Z])/', '$1-$2', $slug );
		$slug = strtolower( $slug );
		
		return $slug;
	}

	/**
	 * Parse array content and extract key-value pairs
	 */
	private function parse_array_content( $array_content, $slug ): array {
		$data = [
			'finding_id'   => $slug,
			'title'        => 'Diagnostic Check',
			'description'  => 'Check description',
			'category'     => 'general',
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'timestamp'    => 'current_time("mysql")',
		];

		// Try to extract existing values
		$pairs = preg_split( '/,(?=[^()]*(?:\([^()]*\))*[^()]*(?:["\']|=>))/', $array_content );

		foreach ( $pairs as $pair ) {
			$pair = trim( $pair );
			
			// Extract key and value
			if ( preg_match( "/['\"]?([a-z_]+)['\"]?\s*=>\s*(.+)/i", $pair, $matches ) ) {
				$key = strtolower( trim( $matches[1], '\'" ' ) );
				$value = trim( $matches[2] );

				// Map old field names to new ones
				if ( $key === 'id' ) {
					$key = 'finding_id';
				}

				// Store if it's a known field
				if ( in_array( $key, array_keys( $data ), true ) ) {
					$data[ $key ] = $value;
				}
			}
		}

		return $data;
	}

	/**
	 * Format return array properly
	 */
	private function format_return_array( $data ): string {
		$lines = [ '[' ];

		foreach ( $data as $key => $value ) {
			// Wrap string values in quotes if not already
			if ( $value === 'current_time("mysql")' ) {
				$formatted_value = "current_time( 'mysql' )";
			} elseif ( is_bool( $value ) || $value === 'true' || $value === 'false' ) {
				$formatted_value = $value === true || $value === 'true' ? 'true' : 'false';
			} elseif ( is_numeric( $value ) ) {
				$formatted_value = $value;
			} else {
				// It's a string, ensure it's quoted
				$formatted_value = "'" . trim( $value, '\'"' ) . "'";
			}

			$lines[] = "\t\t\t'{$key}' => {$formatted_value},";
		}

		$lines[] = "\t\t]";

		return implode( "\n", $lines );
	}
}

// Run the fixer
if ( php_sapi_name() === 'cli' || ! function_exists( 'add_action' ) ) {
	$fixer = new Batch_Diagnostic_Fixer();
	$fixer->fix_all();
} else {
	echo "This script is designed to run from CLI.\n";
	echo "Usage: php tools/batch-diagnostic-fixer.php\n";
}
