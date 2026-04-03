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
	 * Legacy backup directory name used before secret storage was introduced.
	 *
	 * @var string
	 */
	private const LEGACY_BACKUP_DIR_NAME = 'wpshadow-backups';

	/**
	 * Private root directory for Vault Lite backups under uploads.
	 *
	 * @var string
	 */
	private const PRIVATE_ROOT_DIR_NAME = '.wpshadow-vault-lite';

	/**
	 * Option that stores the randomized secret backup directory token.
	 *
	 * @var string
	 */
	private const OPTION_DIRECTORY_TOKEN = 'wpshadow_backup_dir_token';

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
			'directory'              => self::get_backup_directory(),
			'directory_public_label' => self::get_public_location_label(),
			'count'                  => count( $index ),
			'total_size'             => $total_size,
			'total_size_human'       => size_format( $total_size ),
			'last_backup'            => $last,
			'last_backup_label'      => isset( $last['created_at'] ) ? self::format_timestamp( (int) $last['created_at'] ) : __( 'No local backups yet', 'wpshadow' ),
			'last_backup_file'       => isset( $last['file'] ) ? (string) $last['file'] : '',
			'last_backup_status'     => isset( $last['verified'] ) && false === $last['verified'] ? 'warning' : 'ok',
		);
	}

	/**
	 * Get the local backup directory path.
	 *
	 * @since  0.6093.1200
	 * @return string Absolute local backup directory path.
	 */
	public static function get_backup_directory(): string {
		return trailingslashit( self::get_backup_root_directory() ) . self::get_directory_token();
	}

	/**
	 * Get a security-safe public label for the backup location.
	 *
	 * @since  0.6093.1200
	 * @return string Human-friendly location label.
	 */
	public static function get_public_location_label(): string {
		return __( 'Private Vault Lite storage (hidden randomized path)', 'wpshadow' );
	}

	/**
	 * Get the private backup root directory under uploads.
	 *
	 * @since  0.6093.1200
	 * @return string Absolute root directory path.
	 */
	private static function get_backup_root_directory(): string {
		$uploads = wp_get_upload_dir();
		$base    = ! empty( $uploads['basedir'] ) ? (string) $uploads['basedir'] : WP_CONTENT_DIR . '/uploads';

		return trailingslashit( $base ) . self::PRIVATE_ROOT_DIR_NAME;
	}

	/**
	 * Get the legacy predictable backup directory path.
	 *
	 * @since  0.6093.1200
	 * @return string Absolute legacy directory path.
	 */
	private static function get_legacy_backup_directory(): string {
		$uploads = wp_get_upload_dir();
		$base    = ! empty( $uploads['basedir'] ) ? (string) $uploads['basedir'] : WP_CONTENT_DIR . '/uploads';

		return trailingslashit( $base ) . self::LEGACY_BACKUP_DIR_NAME;
	}

	/**
	 * Get or create the randomized token used for the secret backup directory.
	 *
	 * @since  0.6093.1200
	 * @return string Sanitized directory token.
	 */
	private static function get_directory_token(): string {
		$token = self::normalize_directory_token( get_option( self::OPTION_DIRECTORY_TOKEN, '' ) );

		if ( '' === $token ) {
			$token = self::generate_directory_token();

			if ( false === get_option( self::OPTION_DIRECTORY_TOKEN, false ) ) {
				add_option( self::OPTION_DIRECTORY_TOKEN, $token, '', false );
			} else {
				update_option( self::OPTION_DIRECTORY_TOKEN, $token, false );
			}
		}

		return $token;
	}

	/**
	 * Generate a new randomized directory token.
	 *
	 * @since  0.6093.1200
	 * @return string Randomized token.
	 */
	private static function generate_directory_token(): string {
		try {
			return bin2hex( random_bytes( 16 ) );
		} catch ( \Exception $e ) {
			$generated = wp_generate_password( 32, false, false );
			$token     = self::normalize_directory_token( $generated );

			return '' !== $token ? $token : md5( $generated . wp_rand() );
		}
	}

	/**
	 * Normalize a candidate directory token.
	 *
	 * @since  0.6093.1200
	 * @param  mixed $token Raw token value.
	 * @return string Sanitized token or empty string when invalid.
	 */
	private static function normalize_directory_token( $token ): string {
		if ( ! is_string( $token ) ) {
			return '';
		}

		$token = strtolower( preg_replace( '/[^a-z0-9]/', '', $token ) ?? '' );

		return strlen( $token ) >= 24 ? $token : '';
	}

	/**
	 * Ensure the local backup directory exists and is protected from direct browsing.
	 *
	 * @since  0.6093.1200
	 * @return void
	 */
	public static function ensure_backup_directory(): void {
		$root_dir = self::get_backup_root_directory();
		if ( ! is_dir( $root_dir ) ) {
			wp_mkdir_p( $root_dir );
		}
		if ( is_dir( $root_dir ) ) {
			self::write_protection_files( $root_dir );
		}

		$dir = self::get_backup_directory();
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		if ( ! is_dir( $dir ) ) {
			return;
		}

		self::write_protection_files( $dir );
		self::migrate_legacy_backups( $dir );
	}

	/**
	 * Write server-side protection files into a backup directory.
	 *
	 * @since  0.6093.1200
	 * @param  string $dir Absolute directory path.
	 * @return void
	 */
	private static function write_protection_files( string $dir ): void {
		$index_file = trailingslashit( $dir ) . 'index.php';
		if ( ! file_exists( $index_file ) ) {
			file_put_contents( $index_file, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		$htaccess = trailingslashit( $dir ) . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "Options -Indexes\nDeny from all\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}

		$web_config = trailingslashit( $dir ) . 'web.config';
		if ( ! file_exists( $web_config ) ) {
			file_put_contents( $web_config, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n  <system.webServer>\n    <authorization>\n      <remove users=\"*\" roles=\"\" verbs=\"\" />\n      <add accessType=\"Deny\" users=\"*\" />\n    </authorization>\n  </system.webServer>\n</configuration>\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	/**
	 * Move backups out of the old predictable directory into the secret one.
	 *
	 * @since  0.6093.1200
	 * @param  string $target_dir Secret target directory.
	 * @return void
	 */
	private static function migrate_legacy_backups( string $target_dir ): void {
		$legacy_dir = self::get_legacy_backup_directory();

		if ( ! is_dir( $legacy_dir ) || wp_normalize_path( $legacy_dir ) === wp_normalize_path( $target_dir ) ) {
			return;
		}

		$items = glob( trailingslashit( $legacy_dir ) . '*' );
		if ( ! is_array( $items ) || empty( $items ) ) {
			self::write_protection_files( $legacy_dir );
			return;
		}

		$moved_paths = array();
		foreach ( $items as $item_path ) {
			$item_name = basename( (string) $item_path );
			if ( in_array( $item_name, array( 'index.php', 'web.config' ), true ) ) {
				continue;
			}

			$destination = trailingslashit( $target_dir ) . wp_unique_filename( $target_dir, $item_name );
			if ( self::move_file_safely( (string) $item_path, $destination ) ) {
				$moved_paths[ wp_normalize_path( (string) $item_path ) ] = $destination;
			}
		}

		if ( ! empty( $moved_paths ) ) {
			$index = self::get_backup_index();
			foreach ( $index as &$entry ) {
				$entry_path = isset( $entry['path'] ) ? wp_normalize_path( (string) $entry['path'] ) : '';
				if ( isset( $moved_paths[ $entry_path ] ) ) {
					$entry['path'] = $moved_paths[ $entry_path ];
					$entry['file'] = basename( $moved_paths[ $entry_path ] );
				}
			}
			unset( $entry );
			update_option( self::OPTION_INDEX, $index, false );
		}

		self::write_protection_files( $legacy_dir );
	}

	/**
	 * Move a file into the secret backup directory, falling back to copy/delete.
	 *
	 * @since  0.6093.1200
	 * @param  string $source      Source path.
	 * @param  string $destination Destination path.
	 * @return bool True when the move succeeded.
	 */
	private static function move_file_safely( string $source, string $destination ): bool {
		if ( @rename( $source, $destination ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return true;
		}

		if ( @copy( $source, $destination ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@unlink( $source ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return true;
		}

		return false;
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
	 * Get all indexed local backups.
	 *
	 * @since  0.6093.1200
	 * @return array<int,array<string,mixed>> Indexed backup entries.
	 */
	public static function get_backups(): array {
		return self::get_backup_index();
	}

	/**
	 * Get a single indexed backup entry by filename.
	 *
	 * @since  0.6093.1200
	 * @param  string $filename Backup filename.
	 * @return array<string,mixed>|null Matching backup entry or null.
	 */
	public static function get_backup_entry( string $filename ): ?array {
		$target = sanitize_file_name( $filename );

		if ( '' === $target ) {
			return null;
		}

		foreach ( self::get_backup_index() as $entry ) {
			if ( $target === (string) ( $entry['file'] ?? '' ) ) {
				return $entry;
			}
		}

		return null;
	}

	/**
	 * Build a human-readable description for a backup entry.
	 *
	 * @since  0.6093.1200
	 * @param  array<string,mixed> $entry Backup index entry.
	 * @return string Human-readable description.
	 */
	public static function describe_backup( array $entry ): string {
		$trigger_label = self::get_trigger_label( (string) ( $entry['trigger'] ?? 'manual' ) );
		$created_at    = isset( $entry['created_at'] ) ? self::format_timestamp( (int) $entry['created_at'] ) : __( 'unknown time', 'wpshadow' );
		$size_label    = ! empty( $entry['size'] ) ? size_format( (int) $entry['size'] ) : __( 'size unknown', 'wpshadow' );
		$verification  = ! empty( $entry['verified'] ) ? __( 'verified', 'wpshadow' ) : __( 'not verified', 'wpshadow' );

		return sprintf(
			/* translators: 1: trigger label, 2: formatted date/time, 3: formatted size, 4: verification state */
			__( '%1$s created %2$s • %3$s • %4$s', 'wpshadow' ),
			$trigger_label,
			$created_at,
			$size_label,
			$verification
		);
	}

	/**
	 * Restore a local backup archive.
	 *
	 * @since  0.6093.1200
	 * @param  string $filename Backup filename to restore.
	 * @return array<string,mixed> Restore result payload.
	 */
	public static function restore_backup( string $filename ): array {
		$entry = self::get_backup_entry( $filename );
		if ( ! is_array( $entry ) || empty( $entry['path'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'The selected backup could not be found.', 'wpshadow' ),
			);
		}

		$path = (string) $entry['path'];
		if ( ! file_exists( $path ) ) {
			return array(
				'success' => false,
				'message' => __( 'The selected backup file is no longer available on disk.', 'wpshadow' ),
			);
		}

		if ( ! class_exists( '\\ZipArchive' ) ) {
			return array(
				'success' => false,
				'message' => __( 'ZIP support is not available on this server, so the backup cannot be restored automatically.', 'wpshadow' ),
			);
		}

		@set_time_limit( 0 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
		@ignore_user_abort( true ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged

		$safety_backup = self::create_backup(
			array(
				'trigger' => 'pre-restore',
				'context' => (string) ( $entry['file'] ?? $filename ),
			)
		);

		$temp_dir = trailingslashit( self::get_backup_directory() ) . 'restore-temp-' . wp_generate_password( 12, false, false );
		wp_mkdir_p( $temp_dir );

		$zip = new \ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			self::remove_directory_tree( $temp_dir );
			return array(
				'success' => false,
				'message' => __( 'The backup archive could not be opened for restore.', 'wpshadow' ),
			);
		}

		$extracted = $zip->extractTo( $temp_dir );
		$zip->close();

		if ( ! $extracted ) {
			self::remove_directory_tree( $temp_dir );
			return array(
				'success' => false,
				'message' => __( 'The backup archive could not be extracted for restore.', 'wpshadow' ),
			);
		}

		$directory_targets = array(
			$temp_dir . '/site-files/wp-content/plugins'    => WP_CONTENT_DIR . '/plugins',
			$temp_dir . '/site-files/wp-content/themes'     => WP_CONTENT_DIR . '/themes',
			$temp_dir . '/site-files/wp-content/mu-plugins' => WP_CONTENT_DIR . '/mu-plugins',
			$temp_dir . '/site-files/wp-content/uploads'    => WP_CONTENT_DIR . '/uploads',
		);

		foreach ( $directory_targets as $source => $destination ) {
			if ( is_dir( $source ) ) {
				self::copy_directory_tree( $source, $destination );
			}
		}

		$config_dir = $temp_dir . '/site-files/config';
		if ( is_dir( $config_dir ) ) {
			$config_files = glob( trailingslashit( $config_dir ) . '*' );
			if ( is_array( $config_files ) ) {
				foreach ( $config_files as $config_file ) {
					$target = self::get_config_restore_target( basename( (string) $config_file ) );
					if ( '' !== $target ) {
						wp_mkdir_p( dirname( $target ) );
						copy( $config_file, $target ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy
					}
				}
			}
		}

		$database_restored = false;
		$database_dump     = $temp_dir . '/database.sql';
		if ( is_file( $database_dump ) ) {
			$database_restored = self::import_database_dump( $database_dump );
		}

		self::remove_directory_tree( $temp_dir );

		if ( class_exists( Activity_Logger::class ) ) {
			Activity_Logger::log(
				'local_backup_restored',
				sprintf(
					/* translators: %s: restored backup filename */
					__( 'Local backup restored: %s', 'wpshadow' ),
					(string) ( $entry['file'] ?? $filename )
				),
				'backups',
				array(
					'file'              => (string) ( $entry['file'] ?? $filename ),
					'database_restored' => $database_restored,
				)
			);
		}

		return array(
			'success'            => true,
			'message'            => $database_restored
				? __( 'Backup restored successfully. WPShadow created a fresh safety backup first.', 'wpshadow' )
				: __( 'Backup files were restored successfully. WPShadow created a fresh safety backup first, but the database dump was not applied automatically.', 'wpshadow' ),
			'file'               => (string) ( $entry['file'] ?? $filename ),
			'safety_backup_file' => isset( $safety_backup['file'] ) ? sanitize_file_name( (string) $safety_backup['file'] ) : '',
			'database_restored'  => $database_restored,
		);
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
	 * Copy a restored directory tree into its live destination.
	 *
	 * @since  0.6093.1200
	 * @param  string $source      Extracted source directory.
	 * @param  string $destination Live destination directory.
	 * @return void
	 */
	private static function copy_directory_tree( string $source, string $destination ): void {
		if ( ! is_dir( $source ) ) {
			return;
		}

		wp_mkdir_p( $destination );

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $source, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $item ) {
			$target_path = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

			if ( $item->isDir() ) {
				wp_mkdir_p( $target_path );
			} elseif ( $item->isFile() ) {
				wp_mkdir_p( dirname( $target_path ) );
				copy( $item->getPathname(), $target_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy
			}
		}
	}

	/**
	 * Apply a SQL dump created by this backup manager.
	 *
	 * @since  0.6093.1200
	 * @param  string $sql_path Absolute path to the exported SQL file.
	 * @return bool True when the SQL file was imported cleanly.
	 */
	private static function import_database_dump( string $sql_path ): bool {
		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) || ! is_readable( $sql_path ) ) {
			return false;
		}

		$handle = fopen( $sql_path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( false === $handle ) {
			return false;
		}

		$statement = '';
		$success   = true;

		$wpdb->query( 'SET foreign_key_checks = 0' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared

		while ( false !== ( $line = fgets( $handle ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fgets
			$trimmed = trim( $line );

			if ( '' === $trimmed || 0 === strpos( $trimmed, '--' ) ) {
				continue;
			}

			$statement .= $line;

			if ( preg_match( '/;\s*$/', $trimmed ) ) {
				$wpdb->query( $statement ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
				if ( ! empty( $wpdb->last_error ) ) {
					$success = false;
					break;
				}

				$statement = '';
			}
		}

		$wpdb->query( 'SET foreign_key_checks = 1' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		return $success;
	}

	/**
	 * Resolve the live destination path for a restored config file.
	 *
	 * @since  0.6093.1200
	 * @param  string $filename Extracted config filename.
	 * @return string Absolute live destination path.
	 */
	private static function get_config_restore_target( string $filename ): string {
		switch ( $filename ) {
			case 'wp-config.php':
				return file_exists( ABSPATH . 'wp-config.php' ) ? ABSPATH . 'wp-config.php' : dirname( ABSPATH ) . '/wp-config.php';
			case '.htaccess':
				return ABSPATH . '.htaccess';
			default:
				return '';
		}
	}

	/**
	 * Remove a temporary directory tree.
	 *
	 * @since  0.6093.1200
	 * @param  string $directory Directory to remove.
	 * @return void
	 */
	private static function remove_directory_tree( string $directory ): void {
		if ( ! is_dir( $directory ) ) {
			return;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $directory, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $iterator as $item ) {
			if ( $item->isDir() ) {
				rmdir( $item->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
			} else {
				unlink( $item->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_unlink
			}
		}

		rmdir( $directory ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
	}

	/**
	 * Get a user-facing label for the backup trigger type.
	 *
	 * @since  0.6093.1200
	 * @param  string $trigger Trigger slug.
	 * @return string Human-readable label.
	 */
	private static function get_trigger_label( string $trigger ): string {
		switch ( $trigger ) {
			case 'scheduled':
				return __( 'Scheduled backup', 'wpshadow' );
			case 'treatment':
				return __( 'Pre-treatment backup', 'wpshadow' );
			case 'pre-restore':
				return __( 'Safety backup', 'wpshadow' );
			case 'manual':
			default:
				return __( 'Manual backup', 'wpshadow' );
		}
	}

	/**
	 * Get paths that should never be included inside a backup archive.
	 *
	 * @since  0.6093.1200
	 * @return array<int,string> Normalized path prefixes to exclude.
	 */
	private static function get_excluded_paths(): array {
		return array(
			wp_normalize_path( self::get_backup_root_directory() ),
			wp_normalize_path( self::get_legacy_backup_directory() ),
			wp_normalize_path( WP_CONTENT_DIR . '/cache' ),
			wp_normalize_path( WP_CONTENT_DIR . '/upgrade' ),
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
