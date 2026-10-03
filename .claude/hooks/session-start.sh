#!/bin/bash
# Prepares a Claude Code cloud session so the test suite and Duster can run.
# The cloud image ships PHP 8.3 and its network policy blocks the usual PHP 8.4+
# sources (Launchpad PPA, php.net, static-php), so PHP comes from conda-forge
# via micromamba, and GD is compiled from the matching Ubuntu source tarball.
set -euo pipefail

if [ "${CLAUDE_CODE_REMOTE:-}" != "true" ]; then
  exit 0
fi

PHP_PREFIX=/opt/php
MAMBA=/opt/micromamba/bin/micromamba
MICROMAMBA_VERSION=2.9.0-0
PROJECT_DIR="${CLAUDE_PROJECT_DIR:-$(pwd)}"

if [ ! -x "$MAMBA" ]; then
  mkdir -p /opt/micromamba
  curl -fsSL "https://conda.anaconda.org/conda-forge/linux-64/micromamba-${MICROMAMBA_VERSION}.tar.bz2" \
    | tar -xj -C /opt/micromamba bin/micromamba
fi

if [ ! -x "$PHP_PREFIX/bin/php" ]; then
  MAMBA_ROOT_PREFIX=/opt/mamba "$MAMBA" create -y -q -p "$PHP_PREFIX" -c conda-forge --override-channels php
fi

EXT_DIR="$("$PHP_PREFIX/bin/php-config" --extension-dir)"
if [ ! -f "$EXT_DIR/gd.so" ]; then
  PHP_VERSION="$("$PHP_PREFIX/bin/php" -r 'echo PHP_VERSION;')"
  PHP_MINOR="$("$PHP_PREFIX/bin/php" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
  DEBIAN_FRONTEND=noninteractive apt-get install -y -qq libpng-dev libjpeg-dev libfreetype-dev libwebp-dev pkg-config >/dev/null
  mkdir -p /opt/php-src
  if [ ! -d "/opt/php-src/php-${PHP_VERSION}" ]; then
    curl -fsSL "http://archive.ubuntu.com/ubuntu/pool/main/p/php${PHP_MINOR}/php${PHP_MINOR}_${PHP_VERSION}.orig.tar.xz" \
      | tar -xJ -C /opt/php-src
  fi
  (
    cd "/opt/php-src/php-${PHP_VERSION}/ext/gd"
    "$PHP_PREFIX/bin/phpize" >/dev/null
    ./configure -q --with-php-config="$PHP_PREFIX/bin/php-config" --with-jpeg --with-freetype --with-webp >/dev/null
    make -s -j"$(nproc)" >/dev/null
    make -s install >/dev/null
  )
fi

if [ ! -f "$PHP_PREFIX/lib/php.ini" ]; then
  printf 'extension=gd\nmemory_limit=1G\n' > "$PHP_PREFIX/lib/php.ini"
fi

export PATH="$PHP_PREFIX/bin:$PATH"
if [ -n "${CLAUDE_ENV_FILE:-}" ]; then
  echo "export PATH=\"$PHP_PREFIX/bin:\$PATH\"" >> "$CLAUDE_ENV_FILE"
fi

cd "$PROJECT_DIR"

composer install --no-interaction --no-progress

if [ ! -f .env ]; then
  cp .env.example .env
fi
if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --no-interaction --quiet
fi

npm install --no-audit --no-fund
npm run build
