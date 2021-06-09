<?php

/*
 * This file is part of the AllProgrammic ResqueBunde package.
 *
 * (c) AllProgrammic SAS <contact@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllProgrammic\Bundle\ResqueBundle\Controller;

use AllProgrammic\Component\Resque\Worker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WorkersController extends Controller
{
    /**
     * Index action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $workers = $this->get('resque')->getSupervisor()->all();
        $results = [];

        foreach ($workers as $worker) {
            $results[$worker->getHost()][] = $worker;
        }

        ksort($results, SORT_NATURAL | SORT_FLAG_CASE);
        return $this->render('@AllProgrammicResque/workers/index.html.twig', [
            'workers' => $results,
            'total'   => count($workers)
        ]);
    }

    /**
     * View action
     *
     * @param Request $request
     * @param string $host
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, $hostname = 'all')
    {
        $workers = $this->get('resque')->getSupervisor()->all();

        $results = array_map(function (Worker $worker) use ($hostname) {
            if ($worker->getHost() !== $hostname && $hostname !== 'all') {
                return null;
            }

            return $worker;
        }, $workers);

        $results = array_combine($workers, $results);
        $results = array_filter($results);
        uasort($results, function (Worker $a, Worker $b) {
            if ($a->isIdle() == $b->isIdle()) {
                return strcmp($a->getHost(), $b->getHost());
            } else {
                if ($a->isIdle() && !$b->isIdle()) {
                    return 1;
                } else {
                    return -1;
                }
            }
        });

        return $this->render('@AllProgrammicResque/workers/view.html.twig', [
            'workers'   => $results,
            'hostname' => $hostname
        ]);
    }
}
