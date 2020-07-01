<?php

namespace AdamQuaile\Behat\CommandRunnerExtension;

use Behat\Behat\EventDispatcher\Event\BeforeFeatureTeardown;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTeardown;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTeardown;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;

class CommandRunnerSubscriber implements EventSubscriberInterface
{
    private $supportedHooks = [
        'beforeSuite',
        'afterSuite',
        'beforeFeature',
        'afterFeature',
        'beforeScenario',
        'afterScenario'
    ];

    /**
     * @var Process[][]
     */
    private $runningCommands    = [];
    private $registeredCommands = [];

    public function __construct($beforeSuite, $afterSuite, $beforeFeature, $afterFeature, $beforeScenario, $afterScenario)
    {
        foreach ($this->supportedHooks as $hook) {

            $this->registeredCommands[$hook] = [];
            $this->runningCommands[$hook] = [];

            foreach ($$hook as $command) {
                $this->registerCommand($hook, $command);
            }
        }
    }

    private function registerCommand($hook, $command)
    {
        $this->registeredCommands[$hook][] = $command;
    }

    private function setupProcessesForHook($hook)
    {
        foreach ($this->registeredCommands[$hook] as $command) {
            $process = $this->buildProcess($command['command']);

            if ($command['background']) {
                $this->runInBackgroundForHook($process, $hook);
            } else {
                $process->run();
            }
        }
    }

    private function teardownProcessesForHook($hook)
    {
        foreach ($this->runningCommands[$hook] as $process) {
            if ($process->isRunning()) {
                $process->stop();
            }
        }
    }

    private function runInBackgroundForHook(Process $process, $hook)
    {
        $process->start();
        $this->runningCommands[$hook] = $process;
    }

    /**
     * @param $command
     * @return Process
     */
    private function buildProcess($command)
    {
        $arrayCommand = array_filter( explode(" ", $command) );

        return new Process($arrayCommand);
    }

    public function beforeSuite()
    {
        $this->setupProcessesForHook('beforeSuite');
    }

    public function afterSuite()
    {
        $this->teardownProcessesForHook('beforeSuite');
        $this->setupProcessesForHook('afterSuite');

        foreach ($this->supportedHooks as $hook) {
            $this->teardownProcessesForHook($hook);
        }
    }

    public function beforeFeature()
    {
        $this->setupProcessesForHook('beforeFeature');
    }

    public function afterFeature()
    {
        $this->teardownProcessesForHook('beforeFeature');
        $this->setupProcessesForHook('afterFeature');
    }

    public function beforeScenario()
    {
        $this->setupProcessesForHook('beforeScenario');
    }

    public function afterScenario()
    {
        $this->teardownProcessesForHook('beforeScenario');
        $this->setupProcessesForHook('afterScenario');
    }

    public static function getSubscribedEvents()
    {

        return array(
            BeforeSuiteTested::BEFORE   => 'beforeSuite',
            BeforeSuiteTeardown::AFTER  => 'afterSuite',

            BeforeFeatureTested::BEFORE     => 'beforeFeature',
            BeforeFeatureTeardown::AFTER    => 'afterFeature',

            BeforeScenarioTested::BEFORE    => 'beforeScenario',
            BeforeScenarioTeardown::AFTER   => 'afterScenario'
        );
    }

}