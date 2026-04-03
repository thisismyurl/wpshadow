<?php
/**
 * Local Backup Manager.
 *
 * Provides the "Vault Light" local-only backup engine for WPShadow.
 * It creates protected ZIP archives on the same server before treatments
 * run and when scheduled/manual backups are triggered.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since   0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage local backup creation, retention, and status reporting.
 */
class Backup_Manager {

	/**
	 * Option storing indexed local backup metadata.
	 *
	 * @var string
	 */
	private const OPTION_INDEX = 'wpshadow_local_backup_index';

	/**
	 * Option storing the most recent backup result.
	 *
	 * @var string
	 */
	private const OPTION_LAST_RESULT = 'wpshadow_last_backup_result';

	/**
	 * Backup directory name under the uploads folder.
	 *
	 * @var string
	 */
	private const BACKUP_DIR_NAME = 'wpshadow-backups';

	/**
	 * Maximum number of indexed backup records to keep in the option.
	 *
	 * @var int
	 */
	private const INDEX_LIMIT = 50;

	/**
	 * Bootstrap the local backup manager.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wpshadow_before_treatment_apply', array( __CLASS__, 'maybe_backup_before_treatment' ), 10, 3 );
	}

	/**
	 * Create a local backup before non-dry-run treatment execution.
	 *
	 * @since  0.6093.1200
	 * @param  string $class      Treatment class name.
	 * @param  string $finding_id Finding identifier.
	 * @param  bool   $dry_run    Whether the treatment is a dry run.
	 * @return void
	 */
	public static function maybe_backup_before_treatment( string $class, string $finding_id, bool $dry_run = false ): void {
		if ( $dry_run || ! (bool) get_option( 'wpshadow_backup_enabled', true ) ) {
			return;
		}

		$result = self::create_backup(
			array(
				'trigger' => 'treatment',
				'context' => $finding_id,
				'label'   => $class,
			)
		);

		if ( empty( $result['success'] ) && class_exists( Activity_Logger::class ) ) {
			Activity_Logger::log(
				'local_backup_failed',
				sprintf(
					/* translators: 1: treatment class, 2: error message */
					__( 'Local backup failed before %1$s: %2$s', 'wpshadow' ),
					$class,
					(string) ( $result['message'] ?? __( 'Unknown error', 'wpshadow' ) )
				),
				'backups',
				array(
					'finding_id' => $finding_id,
					'class'      => $class,
				)
			);
		}
	}

	/**
	 * Create a protected local backup archive.
	 *
	 * @since  0.6093.1200
	 * @param  array<string,mixed> $args Optional backup context arguments.
	 * @return array<string,mixed> Backup result payload.
	 */
	public static function create_backup( array $args = array() ): array {
		$trigger = isset( $args['trigger'] ) ? sanitize_key( (string) $args['trigger'] ) : 'manual';

		if ( 'treatment' === $trigger && ! (bool) get_option( 'wpshadow_backup_enabled', true ) ) {
			return self::build_failure_result( __( 'Local backups are disabled for treatments.', 'wpshadow' ), $trigger );
		}

		if ( 'scheduled' === $trigger && ! (bool) get_option( 'wpshadow_backup_schedule_enabled', false ) ) {
			return self::build_failure_result( __( 'Scheduled local backups are currently disabled.', 'wpshadow' ), $trigger );
		}

		if ( ! class_exists( '\\ZipArchive' ) ) {
			return self::build_failure_result( __( 'ZIP support is not available on this server, so local backups cannot be created yet.', 'wpshadow' ), $trigger );
		}

		self::ensure_backup_directory();
		$backup_dir = self::get_backup_directory();

		if ( ! is_dir( $backup_dir ) || ! wp_is_writable( $backup_dir ) ) {
			return self::build_failure_result( __( 'The local backup directory is not writable.', 'wpshadow' ), $trigger );
		}

		$timestamp = current_time( 'timestamp' );
		$filename  = wp_unique_filename(
			$backup_dir,
			'wpshadow-backup-' . gmdate( 'Ymd-His', (int) $timestamp ) . '-' . $trigger . '.zip'
		);
		$path      = trailingslashit( $backup_dir ) . $filename;

		$zip         = new \ZipArchive();
		$open_result = $zip->open( $path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );
		if ( true !== $open_result ) {
			return self::build_failure_result( __( 'The backup archive could not be created.', 'wpshadow' ), $trigger );
		}

		$manifest = self::build_manifest( $args, $timestamp );
		$zip->addFromString(
			'manifest.json',
			(string) wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
		);

		if ( (bool) get_option( 'wpshadow_backup_include_database', true ) ) {
			$database_dump = self::export_database_sql();
			if ( '' !== $database_dump ) {
				$zip->addFromString( 'database.sql', $database_dump );
			}
		}

		foreach ( self::get_backup_sources() as $source ) {
			self::add_path_to_zip( $zip, $source['path'], $source['archive_root'] );
		}

		$zip->close();
		clearstatcache( true, $path );

		if ( ! file_exists( $path ) ) {
			return self::build_failure_result( __( 'The backup archive did not finish writing to disk.', 'wpshadow' ), $trigger );
		}

		$size     = (int) filesize( $path );
		$hash     = (string) hash_file( 'sha256', $path );
		$verified = ! (bool) get_option( 'wpshadow_backup_verify', true ) || self::verify_backup( $path );

		$entry = array(
			'file'             => $filename,
			'path'             => $path,
			'trigger'          => $trigger,
			'context'          => isset( $args['context'] ) ? sanitize_text_field( (string) $args['context'] ) : '',
			'label'            => isset( $args['label'] ) ? sanitize_text_field( (string) $args['label'] ) : '',
			'created_at'       => $timestamp,
			'size'             => $size,
			'sha256'           => $hash,
			'verified'         => $verified,
			'include_database' => (bool) get_option( 'wpshadow_backup_include_database', true ),
			'include_uploads'  => (bool) get_option( 'wpshadow_backup_include_uploads', true ),
		);

		$index = self::get_backup_index();
		array_unshift( $index, $entry );
		if ( count( $index ) > self::INDEX_LIMIT ) {
			$index = array_slice( $index, 0, self::INDEX_LIMIT );
		}
		update_option( self::OPTION_INDEX, $index, false );

		$result = array(
			'success'  => $verified,
			'message'  => $verified
				? __( 'Local backup created successfully.', 'wpshadow' )
				: __( 'Local backup was created, but verification did not complete cleanly.', 'wpshadow' ),
			'file'     => $filename,
			'path'     => $path,
			'size'     => $size,
			'formatted_size' => size_format( $size ),
			'verified' => $verified,
			'trigger'  => $trigger,
		);

		update_option( self::OPTION_LAST_RESULT, $result, false );
		self::prune_backups();

		if ( class_exists( Activity_Logger::class ) ) {
			Activity_Logger::log(
				'local_backup_created',
				sprintf(
					/* translators: 1: backup filename, 2: backup size */
					__( 'Local backup created: %1$s (%2$s)', 'wpshadow' ),
					$filename,
					size_format( $size )
				),
				'backups',
				array(
					'file'     => $filename,
					'trigger'  => $trigger,
					'verified' => $verified,
				)
			);
		}

		return $result;
	}

	/**
	 * Get a summary of local backup status for the settings UI.
	 *
	 * @since  0.6093.1200
	 * @return array<string,mixed> Backup status summary.
	 */
	public static function get_status_summary(): array {
		$index      = self::get_backup_index();
		$total_size = 0;
		foreach ( $index as $entry ) {
			$total_size += isset( $entry['size'] ) ? (int) $entry['size'] : 0;
		}

		$last = ! empty( $index ) ? $index[0] : null;

		return array(
			'directory'          => self::get_backup_directory(),
			'count'              => count( $index ),
			'total_size'         => $total_size,
			'total_size_human'   => size_format( $total_size ),
			'last_backup'        => $last,
			'last_backup_label'  => isset( $last['created_at'] ) ? self::format_timestamp( (int) $last['created_at'] ) : __( 'No local backups yet', 'wpshadow' ),
			'last_backup_file'   => isset( $last['file'] ) ? (string) $last['file'] : '',
			'last_backup_status' => isset( $last['verified'] ) && false === $last['verified'] ? 'warning' : 'ok',
		);
	}

	/**
	 * Get the local backup directory path.
	 *
	 * @since  0.6093.1200
	 * @return string Absolute local backup directory path.
	 */
	public static function get_backup_directory(): string {
		$uploads = wp_get_upload_dir();
		$base    = ! empty( $uploads['basedir'] ) ? (string) $uploads['basedir'] : WP_CONTENT_DIR . '/uploads';

		return trailingslashit( $base ) . self::BACKUP_DIR_NAME;
	}

	/**
	 * Ensure the local backup directory exists and is protected from direct browsing.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	public static function ensure_backup_directory(): void {
		$dir = self::get_backup_directory();

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		if ( ! is_dir( $dir ) ) {
			return;
		}

		$index_file = trailingslashit( $dir ) . 'index.php';
		if ( ! file_exists( $index_file ) ) {
			file_put_contents( $index_file, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		$htaccess = trailingslashit( $dir ) . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "Deny from all\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	/**
	 * Verify that a local backup archive is readable and contains expected files.
	 *
	 * @since  0.6093.1200
	 * @param  string $path Absolute path to the archive.
	 * @return bool True when the archive passes basic verification.
	 */
	public static function verify_backup( string $path ): bool {
		if ( ! file_exists( $path ) || 0 >= (int) filesize( $path ) ) {
			return false;
		}

		$zip = new \ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			return false;
		}

		$has_manifest = false !== $zip->locateName( 'manifest.json' );
		$valid        = $zip->numFiles > 0 && $has_manifest;
		$zip->close();

		return $valid;
	}

	/**
	 * Remove expired backups and enforce the total size cap.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	public static function prune_backups(): void {
		$index          = self::get_backup_index();
		$retention_days = max( 1, (int) get_option( 'wpshadow_backup_retention_days', 7 ) );
		$max_size_mb    = max( 50, (int) get_option( 'wpshadow_backup_max_size_mb', 500 ) );
		$cutoff         = current_time( 'timestamp' ) - ( $retention_days * DAY_IN_SECONDS );
		$max_bytes      = $max_size_mb * 1024 * 1024;

		$kept = array();
		foreach ( $index as $entry ) {
			$path       = isset( $entry['path'] ) ? (string) $entry['path'] : '';
			$created_at = isset( $entry['created_at'] ) ? (int) $entry['created_at'] : 0;

			if ( '' === $path || ! file_exists( $path ) ) {
				continue;
			}

			if ( $created_at > 0 && $created_at < $cutoff ) {
				wp_delete_file( $path );
				continue;
			}

			$entry['size'] = (int) filesize( $path );
			$kept[]        = $entry;
		}

		$total = 0;
		foreach ( $kept as $entry ) {
			$total += isset( $entry['size'] ) ? (int) $entry['size'] : 0;
		}

		if ( $total > $max_bytes ) {
			usort(
				$kept,
				static function ( array $left, array $right ): int {
					return (int) ( $left['created_at'] ?? 0 ) <=> (int) ( $right['created_at'] ?? 0 );
				}
			);

			while ( $total > $max_bytes && ! empty( $kept ) ) {
				$oldest = array_shift( $kept );
				$path   = isset( $oldest['path'] ) ? (string) $oldest['path'] : '';
				$size   = isset( $oldest['size'] ) ? (int) $oldest['size'] : 0;

				if ( '' !== $path && file_exists( $path ) ) {
					wp_delete_file( $path );
				}

				$total -= $size;
			}
		}

		usort(
			$kept,
			static function ( array $left, array $right ): int {
				return (int) ( $right['created_at'] ?? 0 ) <=> (int) ( $left['created_at'] ?? 0 );
			}
		);

		update_option( self::OPTION_INDEX, array_values( $kept ), false );
	}

	/**
	 * Load the indexed backup list.
	 *
	 * @since  0.6093.1200
	 * @return array<int,array<string,mixed>> Indexed backup entries.
	 */
	private static function get_backup_index(): array {
		$index = get_option( self::OPTION_INDEX, array() );
		return is_array( $index ) ? $index : array();
	}

	/**
	 * Build the backup manifest payload.
	 *
	 * @since  0.6093.1200
	 * @param  array<string,mixed> $args      Backup arguments.
	 * @param  int                 $timestamp Creation timestamp.
	 * @return array<string,mixed> Manifest payload.
	 */
	private static function build_manifest( array $args, int $timestamp ): array {
		return array(
			'plugin'            => 'wpshadow',
			'version'           => defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : 'unknown',
			'created_at'        => $timestamp,
			'created_at_human'  => self::format_timestamp( $timestamp ),
			'trigger'           => isset( $args['trigger'] ) ? sanitize_key( (string) $args['trigger'] ) : 'manual',
			'context'           => isset( $args['context'] ) ? sanitize_text_field( (string) $args['context'] ) : '',
			'include_database'  => (bool) get_option( 'wpshadow_backup_include_database', true ),
			'include_uploads'   => (bool) get_option( 'wpshadow_backup_include_uploads', true ),
			'compressed'        => true,
			'site_url'          => home_url( '/' ),
			'wp_version'        => get_bloginfo( 'version' ),
			'php_version'       => PHP_VERSION,
		);
	}

	/**
	 * Get the file and directory sources that should be archived.
	 *
	 * @since  0.6093.1200
	 * @return array<int,array{path:string,archive_root:string}>
	 */
	private static function get_backup_sources(): array {
		$sources    = array();
		$wp_content = WP_CONTENT_DIR;

		$directory_map = array(
			$wp_content . '/plugins'    => 'site-files/wp-content/plugins',
			$wp_content . '/themes'     => 'site-files/wp-content/themes',
			$wp_content . '/mu-plugins' => 'site-files/wp-content/mu-plugins',
		);

		if ( (bool) get_option( 'wpshadow_backup_include_uploads', true ) ) {
			$directory_map[ $wp_content . '/uploads' ] = 'site-files/wp-content/uploads';
		}

		foreach ( $directory_map as $path => $archive_root ) {
			if ( is_dir( $path ) ) {
				$sources[] = array(
					'path'         => $path,
					'archive_root' => $archive_root,
				);
			}
		}

		$config_candidates = array(
			ABSPATH . 'wp-config.php',
			dirname( ABSPATH ) . '/wp-config.php',
			ABSPATH . '.htaccess',
		);

		foreach ( $config_candidates as $file ) {
			if ( is_file( $file ) ) {
				$sources[] = array(
					'path'         => $file,
					'archive_root' => 'site-files/config/' . basename( $file ),
				);
			}
		}

		return $sources;
	}

	/**
	 * Add a file or directory tree to the archive.
	 *
	 * @since  0.6093.1200
	 * @param  \ZipArchive $zip          Active ZIP archive.
	 * @param  string      $source_path  Absolute filesystem path.
	 * @param  string      $archive_root Archive path prefix.
	 * @return void
	 */
	private static function add_path_to_zip( \ZipArchive $zip, string $source_path, string $archive_root ): void {
		$normalized_source = wp_normalize_path( $source_path );
		$excluded_paths    = self::get_excluded_paths();

		if ( self::is_excluded_path( $normalized_source, $excluded_paths ) || ! file_exists( $source_path ) ) {
			return;
		}

		if ( is_file( $source_path ) ) {
			$zip->addFile( $source_path, $archive_root );
			return;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $source_path, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $item ) {
			if ( $item->isLink() ) {
				continue;
			}

			$item_path = wp_normalize_path( $item->getPathname() );
			if ( self::is_excluded_path( $item_path, $excluded_paths ) ) {
				continue;
			}

			$relative     = ltrim( str_replace( $normalized_source, '', $item_path ), '/' );
			$archive_path = trim( $archive_root . '/' . $relative, '/' );

			if ( $item->isDir() ) {
				$zip->addEmptyDir( $archive_path );
			} elseif ( $item->isFile() ) {
				$zip->addFile( $item->getPathname(), $archive_path );
			}
		}
	}

	/**
	 * Export the current WordPress database to SQL text.
	 *
	 * @since  0.6093.1200
	 * @return string SQL dump content.
	 */
	private static function export_database_sql(): string {
		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return '';
		}

		$tables = $wpdb->get_col( 'SHOW TABLES' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
		if ( ! is_array( $tables ) || empty( $tables ) ) {
			return '';
		}

		$sql  = "-- WPShadow local backup database export\n";
		$sql .= '-- Generated: ' . gmdate( 'c' ) . "\n\n";

		foreach ( $tables as $table ) {
			$table_name = (string) $table;
			if ( '' === $table_name ) {
				continue;
			}

			$create = $wpdb->get_row( "SHOW CREATE TABLE `{$table_name}`", ARRAY_N ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
			if ( is_array( $create ) && isset( $create[1] ) ) {
				$sql .= 'DROP TABLE IF EXISTS `' . str_replace( '`', '``', $table_name ) . "`;\n";
				$sql .= (string) $create[1] . ";\n\n";
			}

			$rows = $wpdb->get_results( "SELECT * FROM `{$table_name}`", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
			if ( ! is_array( $rows ) || empty( $rows ) ) {
				$sql .= "\n";
				continue;
			}

			foreach ( $rows as $row ) {
				$columns = array_map(
					static function ( string $column ): string {
						return '`' . str_replace( '`', '``', $column ) . '`';
					},
					array_keys( $row )
				);

				$values = array_map( array( __CLASS__, 'sql_export_value' ), array_values( $row ) );
				$sql   .= 'INSERT INTO `' . str_replace( '`', '``', $table_name ) . '` (' . implode( ', ', $columns ) . ') VALUES (' . implode( ', ', $values ) . ");\n";
			}

			$sql .= "\n";
		}

		return $sql;
	}

	/**
	 * Convert a PHP value to a SQL-safe export literal.
	 *
	 * @since  0.6093.1200
	 * @param  mixed $value Raw value.
	 * @return string SQL literal.
	 */
	private static function sql_export_value( $value ): string {
		if ( null === $value ) {
			return 'NULL';
		}

		$escaped = str_replace(
			array( '\\', "\0", "\n", "\r", "\x1a", "'" ),
			array( '\\\\', '\\0', '\\n', '\\r', '\\Z', "\\'" ),
			(string) $value
		);

		return "'{$escaped}'";
	}

	/**
	 * Get paths that should never be included inside a backup archive.
	 *
	 * @since  0.6093.1200
	 * @return array<int,string> Normalized path prefixes to exclude.
	 */
	private static function get_excluded_paths(): array {
		return array(
			wp_normalize_path( self::get_backup_directory() ),
			wp_normalize_path( WP_CONTENT_DIR . '/cache' ),
			wp_normalize_path( ABSPATH . '.git' ),
		);
	}

	/**
	 * Determine whether a path should be excluded from backup archives.
	 *
	 * @since  0.6093.1200
	 * @param  string            $path           Candidate normalized path.
	 * @param  array<int,string> $excluded_paths Excluded path prefixes.
	 * @return bool True when the path should be skipped.
	 */
	private static function is_excluded_path( string $path, array $excluded_paths ): bool {
		foreach ( $excluded_paths as $excluded_path ) {
			if ( '' !== $excluded_path && 0 === strpos( $path, $excluded_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build a standardized failure result payload.
	 *
	 * @since  0.6093.1200
	 * @param  string $message User-facing failure message.
	 * @param  string $trigger Backup trigger slug.
	 * @return array<string,mixed> Failure result payload.
	 */
	private static function build_failure_result( string $message, string $trigger ): array {
		$result = array(
			'success' => false,
			'message' => $message,
			'trigger' => $trigger,
		);

		update_option( self::OPTION_LAST_RESULT, $result, false );

		return $result;
	}

	/**
	 * Format a timestamp using the site's date and time settings.
	 *
	 * @since  0.6093.1200
	 * @param  int $timestamp Unix timestamp.
	 * @return string Human-readable date/time.
	 */
	private static function format_timestamp( int $timestamp ): string {
		return wp_date(
			get_option( 'date_format', 'Y-m-d' ) . ' ' . get_option( 'time_format', 'H:i' ),
			$timestamp
		);
	}
}
