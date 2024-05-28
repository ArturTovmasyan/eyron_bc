#!/bin/bash
app/console doctrine:database:create --env=behat
app/console doctrine:schema:update --force --env=behat
app/console doctrine:fixtures:load --env=behat
cd ../bucketlist/bucketlist && codeceptjs run --steps
