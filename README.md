# Behat Command Runner extension

Extension to run commands at hooks in test lifecycle, useful for cache clearing, 
database setup, teardown, etc...

## Installation

Require package `adamquaile/behat-command-runner-extension` via composer:

    composer require --dev "adamquaile/behat-command-runner-extension *@dev"

Add configuration to your `behat.yml`. This example shows the full configuration options.

    default:
        extensions:
            AdamQuaile\Behat\CommandRunnerExtension:
                  beforeSuite:
                      - echo "beforeSuite"
                      - { command: 'ping example.com', background: true }
                  afterSuite:
                      - echo "afterSuite"
                  beforeFeature:
                      - echo "beforeFeature"
                      - { command: 'ping example.com', background: true }
                  afterFeature:
                      - echo "afterFeature"
                  beforeScenario:
                      - echo "beforeScenario"
                      - { command: 'ping example.com', background: true }
                  afterScenario:
                      - echo "afterScenario"

## A symfony2 example

This example runs `phantomjs` for our javascript behat tests, and also creates 
and recreates a test database for each feature.

For speed, the database is copied back into place after the first run during setup 
rather than using doctrine each time. 

For more isolation, you could do this copying on `beforeScenario` rather than `beforeFeature`.

    default:
        extensions:
            AdamQuaile\Behat\CommandRunnerExtension:
                  beforeSuite:
                      - rm app/var/test.db 
                      - php app/console --env=test doctrine:database:drop --force
                      - php app/console --env=test doctrine:database:create --force
                      - php app/console --env=test doctrine:schema:update --force
                      - cp app/var/test.db app/var/test.initial.db
                      - { command: 'phantomjs-1.9.7-linux-x86_64/bin/phantomjs" --webdriver=4444  >"phantomjs.log"', background: true }
                  beforeFeature:
                      - cp app/var/test.initial.db app/var/test.db
