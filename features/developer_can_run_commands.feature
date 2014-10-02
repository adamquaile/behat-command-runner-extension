Feature: Developer can run commands
  As a developer
  In order to control the test environment
  I need to be able to run commands at certain points throughout test lifecycle

  Scenario: 1 feature, 2 scenarios
    Given I have a file "features/my_features.feature" with contents:
    """
    Feature: Title
     As a
     In order
     I need

     Scenario: A
       Given a

     Scenario: B
       Given b
    """
    And I have a file "features/bootstrap/FeatureContext.php" with contents:
    """
    <?php

    class FeatureContext implements Behat\Behat\Context\Context
    {
        /**
         * @Given a
         */
        public function a()
        {
            echo "a";
        }
        /**
         * @Given b
         */
        public function b()
        {
            echo "b";
        }
    }
    """
    And I have a file "behat.yml" with contents:
    """
    default:
        extensions:
            AdamQuaile\Behat\CommandRunnerExtension:
                  beforeSuite:
                      - echo "beforeSuite"
                  afterSuite:
                      - echo "afterSuite"
                  beforeFeature:
                      - echo "beforeFeature"
                  afterFeature:
                      - echo "afterFeature"
                  beforeScenario:
                      - echo "beforeScenario"
                  afterScenario:
                      - echo "afterScenario"
    """
    When I run behat
