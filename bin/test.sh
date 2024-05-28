#!/bin/bash
app/console doctrine:schema:update --force --env=test
app/console doctrine:fixtures:load --env=test
phpunit -c app/

##!/bin/bash
#app/console doctrine:database:drop --force --env=test
#app/console doctrine:database:create --env=test
#app/console doctrine:schema:update --force --env=test
#app/console doctrine:fixtures:load --env=test --append
#phpunit -c app/