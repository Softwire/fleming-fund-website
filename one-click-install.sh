#!/bin/bash

set -ex

./setup-extra-steps

yarn

./node_modules/wp-install/bin/wp-install

source .credentials/local-database-credentials.sh
./download-latest-db-backup all
