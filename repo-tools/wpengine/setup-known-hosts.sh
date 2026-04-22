#!/usr/bin/env bash

set -euo pipefail

SSH_DIR="${HOME}/.ssh"
KNOWN_HOSTS_TARGET="$SSH_DIR/known_hosts"

if [[ $# -lt 1 ]]; then
	echo "Usage: $0 <hostname> [hostname ...]" >&2
	exit 1
fi

if ! command -v ssh-keyscan >/dev/null 2>&1; then
	echo "Error: ssh-keyscan is required but not installed." >&2
	exit 1
fi

mkdir -p "$SSH_DIR"
chmod 700 "$SSH_DIR"
touch "$KNOWN_HOSTS_TARGET"
chmod 600 "$KNOWN_HOSTS_TARGET"

for host in "$@"; do
	mapfile -t scanned_lines < <(ssh-keyscan -t ed25519 "$host" 2>/dev/null || true)

	if [[ ${#scanned_lines[@]} -eq 0 ]]; then
		echo "Warning: no host key found for $host" >&2
		continue
	fi

	for line in "${scanned_lines[@]}"; do
		[[ -z "$line" ]] && continue
		if ! grep -qxF "$line" "$KNOWN_HOSTS_TARGET"; then
			echo "$line" >> "$KNOWN_HOSTS_TARGET"
		fi
	done
done

echo "Installed known_hosts entries for: $*"