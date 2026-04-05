<?php
/**
 * Database Schema Helpers Trait
 *
 * Shared validation helpers for treatments that must construct bounded DDL
 * statements for known core WordPress tables and index definitions.
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate schema identifiers and whitelisted DDL fragments.
 */
trait Database_Schema_Helpers {
	/**
	 * Validate a table, index, charset, collation, or engine identifier.
	 *
	 * @param string $identifier Raw identifier.
	 * @return string
	 */
	protected static function require_schema_identifier( string $identifier ): string {
		$identifier = trim( $identifier );

		if ( '' === $identifier || 1 !== preg_match( '/^[A-Za-z0-9$_]+$/', $identifier ) ) {
			return '';
		}

		return $identifier;
	}

	/**
	 * Validate a column definition fragment used in a whitelisted index spec.
	 *
	 * @param string $definition Raw index definition.
	 * @return string
	 */
	protected static function require_index_definition( string $definition ): string {
		$definition = trim( $definition );

		if ( '' === $definition || 1 !== preg_match( '/^[A-Za-z0-9_`,\s()]+$/', $definition ) ) {
			return '';
		}

		return $definition;
	}
}
