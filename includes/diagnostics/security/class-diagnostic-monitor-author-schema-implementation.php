<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Author extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-author_schema_implementation', 'title' => __('Author Schema Implementation', 'wpshadow'), 'description' => __('Verifies byline schema on articles. Missing = no author visibility in search.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
