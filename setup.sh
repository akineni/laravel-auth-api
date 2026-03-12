#!/bin/bash

# Make it executable with:
# chmod +x setup.sh
# ================================
# Run it with:
# ./setup.sh
# ================================

# Exit immediately if a command fails
set -e

echo "Starting Laravel setup..."

# Run migrations
php artisan migrate:fresh

# Seed the database
php artisan db:seed

echo "Laravel setup complete!"
