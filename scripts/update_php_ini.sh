#!/usr/bin/env bash

# Get php conf.d path
php_ini_dir=$(php-config --ini-dir)

if [ ! -d "$php_ini_dir" ]; then
    echo "❌ Could not locate php conf.d directory."
    exit 1
fi

echo "✅ Detected php conf.d: $php_ini_dir"

# Copy php.ini files to conf.d directory
cp "$(dirname "$0")"/php/conf.d/app.ini "$php_ini_dir"
if [ $? -ne 0 ]; then
    echo "❌ Failed to copy app.ini to $php_ini_dir."
    exit 1
fi

cp "$(dirname "$0")"/php/conf.d/app.dev.ini "$php_ini_dir"
if [ $? -ne 0 ]; then
    echo "❌ Failed to copy app.dev.ini to $php_ini_dir."
    exit 1
fi

echo "✅ PHP config files copied to conf.d directory."

# Base path to search for PHP extensions
base_dir="/usr/local/lib/php"

if [ ! -d "$base_dir" ]; then
    echo "❌ Directory $base_dir does not exist."
    exit 1
fi

# Find the first directory that contains .so files
first_extension_dir=$(find "$base_dir" -type f -name "*.so" -exec dirname {} \; | sort -u | head -n 1)

if [ -z "$first_extension_dir" ]; then
    echo "❌ No .so extension files found under $base_dir."
    exit 2
fi

echo "📦 Found PHP extension directory: $first_extension_dir"

# Update extension_dir
if grep -q "^extension_dir" "${php_ini_dir}/app.ini"; then
    # Update existing definition
    sed -i "s|^extension_dir\s*=.*|extension_dir = \"$first_extension_dir\"|" "${php_ini_dir}/app.ini"
    echo "✅ Updated existing 'extension_dir' line."
fi