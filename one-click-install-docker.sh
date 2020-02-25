#!/bin/bash

set -ex

yarn

mkdir -p dist/wordpress/wp-content/uploads

./download-latest-db-backup gd gf rf
cp post-live-restore.sql .temp/database-backup/restore/z_post-live-restore.sql

./node_modules/wp-install/bin/wp-install

echo "Set up complete. Run './dev--build-and-watch.sh' and then 'docker-compose up' in a different window"
