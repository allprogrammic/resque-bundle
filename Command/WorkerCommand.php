<?php

/*
 * This file is part of the AllProgrammic ResqueBunde package.
 *
 * (c) AllProgrammic SAS <contact@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllProgrammic\Bundle\ResqueBundle\Command;

use AllProgrammic\Component\Resque\Engine;
use AllProgrammic\Component\Resque\Failure\Redis;
use AllProgrammic\Component\Resque\Heart;
use AllProgrammic\Component\Resque\Lock;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use AllProgrammic\Component\Resque\Worker;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WorkerCommand extends Command
{
    /**
     * @var Engine
     */
    private $resque;

    /**
     * @var Heart
     */
    private $resqueHeartbeat;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Redis
     */
    private $failureQueue;

    /**
     * @var Lock
     */
    private $lockDelayed;

    /**
     * @var int
     */
    private $sleepInterval;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $pids = [];

    /**
     * @var bool
     */
    private $shutdown = false;

    /**
     * WorkerCommand constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        Engine $resque,
        Heart $resqueHeartbeat,
        EventDispatcherInterface $eventDispatcher,
        Redis $failureQueue,
        Lock $lockDelayed,
        int $interval,
        LoggerInterface $logger
    ) {
        parent::__construct();

        $this->resque = $resque;
        $this->resqueHeartbeat = $resqueHeartbeat;
        $this->eventDispatcher = $eventDispatcher;
        $this->failureQueue = $failureQueue;
        $this->lockDelayed = $lockDelayed;
        $this->sleepInterval = $interval;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('resque:worker')
            ->setDefinition([
                new InputArgument('queues', InputArgument::REQUIRED, 'queues (comma separated)'),
                new InputOption('count', null, InputOption::VALUE_OPTIONAL, 'number of worker to start', 1),
                new InputOption('blocking', null, InputOption::VALUE_OPTIONAL, 'use blocking mode', false),
                new InputOption('interval', null, InputOption::VALUE_OPTIONAL, 'interval', null),
                new InputOption('pidfile', null, InputOption::VALUE_OPTIONAL, 'pidfile', null),
                new InputOption('cyclic', null, InputOption::VALUE_OPTIONAL, 'use cylic mode for queues', false),
            ])
        ;
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queues   = explode(',', $input->getArgument('queues'));

        $count    = (int) $input->getOption('count');
        $interval = $input->getOption('interval');
        $blocking = $input->getOption('blocking');
        $pidfile  = $input->getOption('pidfile');
        $cyclic   = $input->getOption('cyclic');

        $blocking = filter_var($blocking, FILTER_VALIDATE_BOOLEAN);
        $cyclic   = filter_var($cyclic, FILTER_VALIDATE_BOOLEAN);

        if (is_null($interval)) {
            $interval = $this->sleepInterval;
        }

        if ($pidfile && false === file_put_contents($pidfile, getmypid())) {
            throw new \RuntimeException(sprintf('Could not write PID information to %s', $pidfile));
        }

        if ($count > 1) {
            try {
                return $this->spawnWorkers($count, $queues, $interval, $blocking, $pidfile, $cyclic);
            } catch (\Exception $ex) {
                $this->shutdown();
            }
        }

        $this->createWorker($queues, $interval, $blocking, $pidfile, $cyclic);
    }

    /**
     * Spawn workers
     *
     * @param $count
     * @param $queues
     * @param $interval
     * @param $blocking
     * @param $pidfile
     * @param $cyclic
     */
    private function spawnWorkers($count, $queues, $interval, $blocking, $pidfile, $cyclic)
    {
        $status = null;
        $master = true;

        if (!function_exists('pcntl_signal')) {
            return;
        }

        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT,  [$this, 'shutdown']);
        pcntl_signal(SIGQUIT, [$this, 'shutdown']);

        for ($i = 0; $i < $count; ++$i) {
            pcntl_signal_dispatch();

            if ($this->shutdown) {
                break;
            }

            $pid = pcntl_fork();

            if ($pid === -1) {
                throw new \RuntimeException(sprintf('Could not fork worker %s', $i));
            }

            if ($pid === 0 || $pid === null) {
                $master = false;
                $this->pids[] = $pid;
                $this->createWorker($queues, $interval, $blocking, $pidfile, $cyclic);
                exit(0);
            }
        }

        if ($master) {
            pcntl_wait($status);
        }
    }

    public function shutdown()
    {
        $this->shutdown = true;

        foreach ($this->pids as $pid) {
            posix_kill($pid, SIGQUIT);
        }

        exit(0);
    }

    /**
     * Create worker
     *
     * @param $queues
     * @param $interval
     * @param $blocking
     * @param $pidfile
     * @param $cyclic
     */
    private function createWorker($queues, $interval, $blocking, $pidfile, $cyclic)
    {
        if (is_null($this->logger)) {
            throw new \RuntimeException('Could not get logger');
        }

        $worker = new Worker(
            $this->resque,
            $this->resqueHeartbeat,
            $this->eventDispatcher,
            $this->failureQueue,
            $this->lockDelayed,
            $queues,
            $cyclic,
            $this->logger
        );

        $this->logger->log(LogLevel::NOTICE, sprintf('Starting worker %s', $worker));
        $worker->work($interval, $blocking);
    }
}
