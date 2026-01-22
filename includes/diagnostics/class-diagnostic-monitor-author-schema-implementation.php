<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Author-schema-implementation {
  public static function check() {
    return ['id' => 'monitor-author_schema_implementation', 'title' => __('Author Schema Implementation', 'wpshadow'), 'description' => __('Verifies byline schema on articles. Missing = no author visibility in search.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
