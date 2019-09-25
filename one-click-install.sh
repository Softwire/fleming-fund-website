#!/bin/bash

set -ex

yarn

./setup-extra-steps

./node_modules/wp-install/bin/wp-install

source .credentials/local-database-credentials.sh
./download-latest-db-backup all
