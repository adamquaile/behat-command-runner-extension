<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Process\Process;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    private static $rootDir;

    private static $workingDir;

    /**
     * @var Process
     */
    private $process;

    public function __construct()
    {

    }

    /**
     * @BeforeSuite
     */
    public static function beforeSuite()
    {
        self::$rootDir = __DIR__ . '/../../';
        self::$workingDir = self::$rootDir . 'workingDir/';

        if (!file_exists(self::$workingDir)) {
            mkdir(self::$workingDir);
        }

        exec('rm -rf ' . escapeshellarg(self::$workingDir) . '/*');
        exec('cp -R ' . escapeshellarg(self::$rootDir .'/vendor') .' ' . escapeshellarg(self::$workingDir.'/vendor'));
    }


    /**
     * @Given I have a file :filename with contents:
     */
    public function iHaveAFileWithContents($filename, PyStringNode $string)
    {
        $filename = self::$workingDir . '/' . $filename;

        $folder = dirname($filename);
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        file_put_contents($filename, $string->getRaw());
    }

    /**
     * @When I run behat
     */
    public function iRunBehat()
    {
        $process = new \Symfony\Component\Process\Process(sprintf('php %s', self::$workingDir.'/vendor/behat/behat/bin/behat'), self::$workingDir);
        $process->start();
        $process->wait();
        $this->process = $process;

        echo $this->process->getErrorOutput();exit;

//        require self::$workingDir.'/features/Feature2Context.php';
//        chdir(self::$workingDir);
//
//        $factory = new \Behat\Behat\ApplicationFactory();
//        $factory->createApplication()->run();
    }
}
