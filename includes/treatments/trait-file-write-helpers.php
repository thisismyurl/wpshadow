<?php
/**
 * File Write Helpers Trait
 *
 * Shared helper methods for treatments that write to wp-config.php or
 * .htaccess. Used by all four Tier B file-write treatments.
 *
 * Each written block is wrapped in unique marker comments so we can later
 * find and remove exactly what we added without touching anything else.
 *
 * Marker format (wp-config.php):
 *   // WPSHADOW_MARKER_START: {slug}
 *   define('CONSTANT', value);
 *   // WPSHADOW_MARKER_END: {slug}
 *
 * Marker format (.htaccess):
 *   # WPSHADOW_MARKER_START: {slug}
 *   <block>
 *   # WPSHADOW_MARKER_END: {slug}
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6095
 */

namespace WPShadow\Treatments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared file-manipulation helpers for file-write treatments.
 */
trait File_Write_Helpers {
	/**
	 * Bootstrap the WordPress filesystem API for managed file operations.
	 *
	 * @return \WP_Filesystem_Base|null
	 */
	protected static function get_managed_wp_filesystem() {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		global $wp_filesystem;

		if ( $wp_filesystem instanceof \WP_Filesystem_Base ) {
			return $wp_filesystem;
		}

		if ( ! function_exists( 'WP_Filesystem' ) || ! WP_Filesystem() ) {
			return null;
		}

		return $wp_filesystem instanceof \WP_Filesystem_Base ? $wp_filesystem : null;
	}

	/**
	 * Determine whether a managed file path is writable.
	 *
	 * These treatments modify canonical WordPress-managed files directly after
	 * passing the plugin's explicit file-write review and trust checks.
	 *
	 * @param string $file_path Absolute file path.
	 * @return bool
	 */
	protected static function is_managed_file_writable( string $file_path ): bool {
		return wp_is_writable( $file_path );
	}

	/**
	 * Read a managed file directly from disk.
	 *
	 * @param string $file_path Absolute file path.
	 * @return string|false
	 */
	protected static function read_managed_file_contents( string $file_path ) {
		$filesystem = self::get_managed_wp_filesystem();
		if ( ! $filesystem ) {
			return false;
		}

		return $filesystem->get_contents( $file_path );
	}

	/**
	 * Write managed file content directly to disk.
	 *
	 * @param string $file_path Absolute file path.
	 * @param string $content   New file content.
	 * @return bool
	 */
	protected static function write_managed_file_contents( string $file_path, string $content ) {
		$filesystem = self::get_managed_wp_filesystem();
		if ( ! $filesystem ) {
			return false;
		}

		return (bool) $filesystem->put_contents( $file_path, $content, FS_CHMOD_FILE );
	}

	// =========================================================================
	// wp-config.php helpers
	// =========================================================================

	/**
	 * Insert (or update) a marker-wrapped define() block in wp-config.php.
	 *
	 * Inserts after the opening `<?php` tag. If a marker for this slug
	 * already exists it is replaced in-place, not duplicated.
	 *
	 * @param string $file_path   Absolute path to wp-config.php.
	 * @param string $slug        Unique marker slug (e.g. 'autosave-interval').
	 * @param string $define_line The define() statement (no trailing newline needed).
	 * @return array{success:bool, message:string}
	 */
	protected static function write_wp_config_define( string $file_path, string $slug, string $define_line ): array {
		if ( ! file_exists( $file_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'wp-config.php not found.', 'wpshadow' ),
			);
		}

		if ( ! is_readable( $file_path ) || ! self::is_managed_file_writable( $file_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'wp-config.php is not readable/writable. Please check file permissions.', 'wpshadow' ),
			);
		}

		$content = self::read_managed_file_contents( $file_path );
		if ( false === $content ) {
			return array(
				'success' => false,
				'message' => __( 'Could not read wp-config.php.', 'wpshadow' ),
			);
		}

		$marker_start = "// WPSHADOW_MARKER_START: {$slug}";
		$marker_end   = "// WPSHADOW_MARKER_END: {$slug}";
		$block        = "\n{$marker_start}\n{$define_line}\n{$marker_end}\n";

		// If marker already exists, replace the existing block.
		$pattern = '/\n\/\/ WPSHADOW_MARKER_START: ' . preg_quote( $slug, '/' ) . '\n.*?\n\/\/ WPSHADOW_MARKER_END: ' . preg_quote( $slug, '/' ) . '\n/s';
		if ( preg_match( $pattern, $content ) ) {
			$new_content = preg_replace( $pattern, $block, $content );
		} else {
			// Insert after `<?php` on the first line.
			$new_content = preg_replace( '/^<\?php/', '<?php' . $block, $content, 1 );
		}

		if ( null === $new_content ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to build new wp-config.php content.', 'wpshadow' ),
			);
		}

		$written = self::write_managed_file_contents( $file_path, $new_content );
		if ( false === $written ) {
			return array(
				'success' => false,
				'message' => __( 'Could not write to wp-config.php. Please check file permissions.', 'wpshadow' ),
			);
		}

		// Invalidate opcode cache if available.
		if ( function_exists( 'opcache_invalidate' ) ) {
			opcache_invalidate( $file_path, true );
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: the define() statement written */
				__( 'Successfully added to wp-config.php: %s', 'wpshadow' ),
				$define_line
			),
		);
	}

	/**
	 * Remove a marker-wrapped block from wp-config.php.
	 *
	 * @param string $file_path Absolute path to wp-config.php.
	 * @param string $slug      Marker slug used when the block was written.
	 * @return array{success:bool, message:string}
	 */
	protected static function remove_wp_config_block( string $file_path, string $slug ): array {
		if ( ! file_exists( $file_path ) ) {
			return array(
				'success' => true,
				'message' => __( 'Nothing to remove (file not found).', 'wpshadow' ),
			);
		}

		if ( ! is_readable( $file_path ) || ! self::is_managed_file_writable( $file_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'wp-config.php is not readable/writable. Please check file permissions.', 'wpshadow' ),
			);
		}

		$content = self::read_managed_file_contents( $file_path );
		if ( false === $content ) {
			return array(
				'success' => false,
				'message' => __( 'Could not read wp-config.php.', 'wpshadow' ),
			);
		}

		$pattern     = '/\n\/\/ WPSHADOW_MARKER_START: ' . preg_quote( $slug, '/' ) . '\n.*?\n\/\/ WPSHADOW_MARKER_END: ' . preg_quote( $slug, '/' ) . '\n/s';
		$new_content = preg_replace( $pattern, '', $content );

		if ( $new_content === $content ) {
			// Block was not present — nothing to do.
			return array(
				'success' => true,
				'message' => __( 'Block not present — nothing to remove.', 'wpshadow' ),
			);
		}

		$written = self::write_managed_file_contents( $file_path, $new_content );
		if ( false === $written ) {
			return array(
				'success' => false,
				'message' => __( 'Could not write to wp-config.php.', 'wpshadow' ),
			);
		}

		if ( function_exists( 'opcache_invalidate' ) ) {
			opcache_invalidate( $file_path, true );
		}

		return array(
			'success' => true,
			'message' => __( 'Block removed from wp-config.php successfully.', 'wpshadow' ),
		);
	}

	// =========================================================================
	// .htaccess helpers
	// =========================================================================

	/**
	 * Append (or replace) a marker-wrapped block in .htaccess.
	 *
	 * @param string $file_path    Absolute path to .htaccess.
	 * @param string $slug         Unique marker slug.
	 * @param string $htaccess_block  The raw rule(s) to add (no trailing newline needed).
	 * @return array{success:bool, message:string}
	 */
	protected static function write_htaccess_block( string $file_path, string $slug, string $htaccess_block ): array {
		if ( file_exists( $file_path ) && ! self::is_managed_file_writable( $file_path ) ) {
			return array(
				'success' => false,
				'message' => __( '.htaccess is not writable. Please check file permissions.', 'wpshadow' ),
			);
		}

		$existing = '';
		if ( file_exists( $file_path ) && is_readable( $file_path ) ) {
			$existing = (string) self::read_managed_file_contents( $file_path );
		}

		$marker_start = "# WPSHADOW_MARKER_START: {$slug}";
		$marker_end   = "# WPSHADOW_MARKER_END: {$slug}";
		$block        = "\n{$marker_start}\n{$htaccess_block}\n{$marker_end}\n";

		$pattern = '/\n# WPSHADOW_MARKER_START: ' . preg_quote( $slug, '/' ) . '\n.*?\n# WPSHADOW_MARKER_END: ' . preg_quote( $slug, '/' ) . '\n/s';
		if ( preg_match( $pattern, $existing ) ) {
			$new_content = preg_replace( $pattern, $block, $existing );
		} else {
			$new_content = $existing . $block;
		}

		$written = self::write_managed_file_contents( $file_path, $new_content );
		if ( false === $written ) {
			return array(
				'success' => false,
				'message' => __( 'Could not write to .htaccess. Please check file permissions.', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: file path */
				__( 'Successfully updated .htaccess: %s', 'wpshadow' ),
				$file_path
			),
		);
	}

	/**
	 * Remove a marker-wrapped block from .htaccess.
	 *
	 * @param string $file_path Absolute path to .htaccess.
	 * @param string $slug      Marker slug.
	 * @return array{success:bool, message:string}
	 */
	protected static function remove_htaccess_block( string $file_path, string $slug ): array {
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return array(
				'success' => true,
				'message' => __( 'Nothing to remove.', 'wpshadow' ),
			);
		}

		if ( ! self::is_managed_file_writable( $file_path ) ) {
			return array(
				'success' => false,
				'message' => __( '.htaccess is not writable.', 'wpshadow' ),
			);
		}

		$content     = self::read_managed_file_contents( $file_path );
		$pattern     = '/\n# WPSHADOW_MARKER_START: ' . preg_quote( $slug, '/' ) . '\n.*?\n# WPSHADOW_MARKER_END: ' . preg_quote( $slug, '/' ) . '\n/s';
		$new_content = preg_replace( $pattern, '', (string) $content );

		if ( $new_content === $content ) {
			return array(
				'success' => true,
				'message' => __( 'Block not present — nothing to remove.', 'wpshadow' ),
			);
		}

		self::write_managed_file_contents( $file_path, (string) $new_content );

		return array(
			'success' => true,
			'message' => __( 'Block removed from .htaccess successfully.', 'wpshadow' ),
		);
	}
}
