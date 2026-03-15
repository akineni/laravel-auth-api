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

# Run migrations and seed the database
php artisan migrate:fresh --seed

echo "Laravel setup complete!"
