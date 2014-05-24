#!/bin/bash

base=$(cd "$(dirname "$0")/.."; pwd)

export COMPOSER_HOME=$base/home
#export COMPOSER_SHARED_DIR=$COMPOSER_HOME/shared

../../vendor/bin/composer "$@"
