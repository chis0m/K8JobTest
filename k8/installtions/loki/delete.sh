#!/bin/bash

SCRIPT_DIR=$(dirname "$(realpath "${BASH_SOURCE[0]}")")

echo "The script directory is: $SCRIPT_DIR"

# Delete Helm release
helm uninstall grafana -n grafana
helm uninstall loki -n loki
helm uninstall promtail -n loki

echo "Cleanup completed successfully."
