<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Security_Disallow_File_Edit extends Diagnostic_Base {

	public static function check(): ?array {
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
			return array(
				'id'           => 'file-edit-allowed',
				'title'        => 'DISALLOW_FILE_EDIT not set',
				'threat_level' => 60,
			);
		}
		return null;
	}

	public static function test_live_security_disallow_file_edit(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'File edit disabled' : 'File edit allowed',
		);
	}
}
