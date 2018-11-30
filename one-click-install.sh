#!/bin/bash

set -x

./setup-extra-steps

yarn

./node_modules/wp-install/bin/wp-install

./download-latest-db-backup all
