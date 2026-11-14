#!/usr/bin/env bash
# Builds the WordPress plugin zip: release/grant-publishing-site-<version>.zip
set -euo pipefail
cd "$(dirname "$0")/.."
VERSION=$(node -p "require('./package.json').version")
python3 tools/build-css.py
STAGE=$(mktemp -d)
PLUGIN="$STAGE/grant-publishing-site"
mkdir -p "$PLUGIN" release
for item in grant-publishing-site.php README.md pages.json client-recommendations.json client-video.json design-portfolio.json interior-portfolio.json luma-interior-excerpts.json native-copy-updates.json assets drafts elementor includes partials templates; do
  [ -e "$item" ] && cp -R "$item" "$PLUGIN/"
done
rm -f "$PLUGIN"/assets/*.src.css
(cd "$STAGE" && zip -qr "grant-publishing-site-$VERSION.zip" grant-publishing-site)
mv "$STAGE/grant-publishing-site-$VERSION.zip" "release/"
rm -rf "$STAGE"
echo "Created release/grant-publishing-site-$VERSION.zip"
