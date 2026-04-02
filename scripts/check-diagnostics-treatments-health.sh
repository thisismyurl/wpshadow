#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

cd "$ROOT_DIR"

echo "WPShadow Diagnostics/Treatments Health Check"
echo "Root: $ROOT_DIR"
echo

count_files() {
  local glob_dir="$1"
  local pattern="$2"
  find "$glob_dir" -type f -name "$pattern" | wc -l | awk '{print $1}'
}

list_duplicate_basenames() {
  local glob_dir="$1"
  local pattern="$2"
  find "$glob_dir" -type f -name "$pattern" -printf '%f\n' | sort | uniq -d
}

list_duplicate_class_names() {
  local glob_dir="$1"
  local class_prefix="$2"
  grep -RhoP "class\s+(${class_prefix}[A-Za-z0-9_]+)" "$glob_dir" --include='*.php' | awk '{print $2}' | sort | uniq -d
}

diag_count="$(count_files includes/diagnostics 'class-diagnostic-*.php')"
treat_count="$(count_files includes/treatments 'class-treatment-*.php')"

echo "Counts"
echo "- Diagnostics files: $diag_count"
echo "- Treatments files: $treat_count"
echo

diag_dup_base="$(list_duplicate_basenames includes/diagnostics 'class-diagnostic-*.php' || true)"
treat_dup_base="$(list_duplicate_basenames includes/treatments 'class-treatment-*.php' || true)"

diag_dup_class="$(list_duplicate_class_names includes/diagnostics 'Diagnostic_' || true)"
treat_dup_class="$(list_duplicate_class_names includes/treatments 'Treatment_' || true)"

echo "Collision checks"
if [[ -n "$diag_dup_base" ]]; then
  echo "- Duplicate diagnostic basenames found:"
  echo "$diag_dup_base" | sed 's/^/  - /'
else
  echo "- Duplicate diagnostic basenames: none"
fi

if [[ -n "$treat_dup_base" ]]; then
  echo "- Duplicate treatment basenames found:"
  echo "$treat_dup_base" | sed 's/^/  - /'
else
  echo "- Duplicate treatment basenames: none"
fi

if [[ -n "$diag_dup_class" ]]; then
  echo "- Duplicate diagnostic class names found:"
  echo "$diag_dup_class" | sed 's/^/  - /'
else
  echo "- Duplicate diagnostic class names: none"
fi

if [[ -n "$treat_dup_class" ]]; then
  echo "- Duplicate treatment class names found:"
  echo "$treat_dup_class" | sed 's/^/  - /'
else
  echo "- Duplicate treatment class names: none"
fi

echo

if command -v php >/dev/null 2>&1; then
  echo "Syntax checks (php -l)"
  diag_fail=0
  treat_fail=0

  while IFS= read -r file; do
    if ! php -l "$file" >/dev/null 2>&1; then
      echo "  - Diagnostic lint failure: $file"
      diag_fail=$((diag_fail + 1))
    fi
  done < <(find includes/diagnostics -type f -name 'class-diagnostic-*.php' | sort)

  while IFS= read -r file; do
    if ! php -l "$file" >/dev/null 2>&1; then
      echo "  - Treatment lint failure: $file"
      treat_fail=$((treat_fail + 1))
    fi
  done < <(find includes/treatments -type f -name 'class-treatment-*.php' | sort)

  echo "- Diagnostic lint failures: $diag_fail"
  echo "- Treatment lint failures: $treat_fail"
else
  echo "Syntax checks"
  echo "- php executable not found in PATH; skipping php -l checks"
fi

echo
echo "Done."
