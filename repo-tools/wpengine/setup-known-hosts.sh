#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
KNOWN_HOSTS_SOURCE="$SCRIPT_DIR/known_hosts"
SSH_DIR="${HOME}/.ssh"
KNOWN_HOSTS_TARGET="$SSH_DIR/known_hosts"

mkdir -p "$SSH_DIR"
chmod 700 "$SSH_DIR"
touch "$KNOWN_HOSTS_TARGET"
chmod 600 "$KNOWN_HOSTS_TARGET"

while IFS= read -r line || [[ -n "$line" ]]; do
	[[ -z "$line" ]] && continue
	if ! grep -qxF "$line" "$KNOWN_HOSTS_TARGET"; then
		echo "$line" >> "$KNOWN_HOSTS_TARGET"
	fi
done < "$KNOWN_HOSTS_SOURCE"

echo "Installed WP Engine known_hosts entries from $KNOWN_HOSTS_SOURCE"