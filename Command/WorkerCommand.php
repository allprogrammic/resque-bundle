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

use Psr\Log\LogLevel;
use AllProgrammic\Component\Resque\Worker;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends ContainerAwareCommand
{
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
        $queues = explode(',', $input->getArgument('queues'));
        $interval = $input->getOption('interval');
        $blocking = $input->getOption('blocking');
        $pidfile = $input->getOption('pidfile');

        if (is_null($interval)) {
            $interval = $this->getContainer()->getParameter('resque_worker_sleeping');
        }

        if ($pidfile && false === file_put_contents($pidfile, getmypid())) {
            throw new \RuntimeException(sprintf('Could not write PID information to %s', $pidfile));
        }

        if ($count = $input->getOption('count') > 1) {
            $this->spawnWorkers($count, $queues, $interval, $blocking, $pidfile);

            return;
        }

        $this->createWorker($queues, $interval, $blocking, $pidfile);
    }

    /**
     * Spawn workers
     *
     * @param $count
     * @param $queues
     * @param $interval
     * @param $blocking
     * @param $pidfile
     */
    private function spawnWorkers($count, $queues, $interval, $blocking, $pidfile)
    {
        for ($i = 0; $i < $count; ++$i) {
            $pid = $this->getContainer()->get('resque')->fork();

            if ($pid == -1) {
                throw new \RuntimeException(sprintf('Could not fork worker %', $i));
            }

            if (!$pid) {
                // Child, start the worker
                $this->createWorker($queues, $interval, $blocking, $pidfile);
                break;
            }
        }
    }

    /**
     * Create worker
     *
     * @param $queues
     * @param $interval
     * @param $blocking
     * @param $pidfile
     */
    private function createWorker($queues, $interval, $blocking, $pidfile)
    {
        $worker = new Worker(
            $this->getContainer()->get('resque'),
            $this->getContainer()->get('resque.heart'),
            $this->getContainer()->get('event_dispatcher'),
            $this->getContainer()->get('resque.failure'),
            $this->getContainer()->get('resque.lock_delayed'),
            $queues,
            $logger = $this->getContainer()->get('logger')
        );

        $logger->log(LogLevel::NOTICE, sprintf('Starting worker %s', $worker));
        $worker->work($interval, $blocking);
    }
}
