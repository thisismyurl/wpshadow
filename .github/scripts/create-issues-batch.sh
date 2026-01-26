#!/bin/bash
# Quick script to create issues in batches
# Usage: ./create-issues-batch.sh [start_number] [batch_size]

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
START=${1:-1}
BATCH=${2:-10}

echo "Creating batch of $BATCH issues starting from #$START"
echo ""

python3 "$SCRIPT_DIR/generate-diagnostic-issues.py" --batch "$BATCH" --start "$START"

NEXT=$((START + BATCH))
echo ""
echo "✅ Batch complete!"
echo ""
echo "To continue with the next batch, run:"
echo "  ./create-issues-batch.sh $NEXT $BATCH"
