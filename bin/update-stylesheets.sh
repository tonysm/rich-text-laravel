#!/usr/bin/env bash

set -euo pipefail

echo "Updating npm dependencies..."
npm update

echo "Building stylesheets..."
npm run build

echo "Done."
