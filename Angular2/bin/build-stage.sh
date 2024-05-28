#!/bin/bash

./bin/make-desc.sh

ng build --env=stage --output-path=../web/angular2/dist --aot true

./bin/make-mobile.sh

ng build --env=stage --output-path=../web/angular2/mobile --aot true

./bin/clean.sh
