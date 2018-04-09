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
use Symfony\Component\Yaml\Yaml;

class RecurringCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('resque:recurring:load')
            ->setDefinition([
                new InputArgument('files', InputArgument::REQUIRED, 'File path with your own recurring job tasks'),
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
        $files = explode(',', $input->getArgument('files'));

        foreach ($files as $file) {
            try {
                $jobs = Yaml::parse(file_get_contents($file));
            } catch (\Exception $ex) {
                $jobs = null;
            }

            if (null === $jobs) {
                exit;
            }

            foreach ($jobs as $key => $result) {
                if (empty($result['cron'])) {
                    throw new \RuntimeException(sprintf('You must define cron parameters for %s task', $key));
                }

                if (empty($result['class'])) {
                    throw new \RuntimeException(sprintf('You must define class parameters for % task', $key));
                }

                if (empty($result['queue'])) {
                    throw new \RuntimeException(sprintf('You must define queue parameters for %s task', $key));
                }

                if (empty($result['args'])) {
                    throw new \RuntimeException(sprintf('You must define args parameters for %s task', $key));
                }

                if (empty($result['description'])) {
                    $result['description'] = '';
                }

                $result['name'] = $key;

                $this->getContainer()->get('resque')->insertRecurringJobs($result);
            }
        }
    }
}
