#!/usr/bin/env bash

terraform state list | xargs -L 1 terraform state rm
