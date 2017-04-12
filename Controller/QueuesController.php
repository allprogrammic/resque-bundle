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

class QueuesController extends Controller
{
    public function indexAction()
    {
        return $this->render('AllProgrammicResqueBundle:queues:index.html.twig');
    }

    public function viewAction(Request $request, $id)
    {
        $queueSize = $this->get('resque')->size($id);
        $jobPerPage = 25;
        $offset = $request->query->get('start', 0);

        if ($offset > $queueSize) {
            $offset = $queueSize - $jobPerPage;
        }

        $count = $jobPerPage;
        $maxOffset = $offset + $count;
        if ($maxOffset > $queueSize) {
            $count -= $maxOffset - $queueSize;
        }

        $jobs = $this->get('resque')->peekInQueue(
            $id,
            $offset,
            $count
        );

        return $this->render('AllProgrammicResqueBundle:queues:view.html.twig', [
            'queueId' => $id,
            'jobs' => $jobs,
            'startAt' => $offset + 1,
            'endAt' => $offset + $count,
            'size' => $queueSize,
            'previous' => $offset - $jobPerPage,
        ]);
    }

    public function queuesAction()
    {
        $resque = $this->get('resque');

        $queues = [];
        foreach ($resque->queues() as $queue) {
            $queues[$queue] = $resque->size($queue);
        }


        return $this->render('AllProgrammicResqueBundle:queues:_queues.html.twig', [
            'queues' => $queues,
            'failedSize' => $resque->size('failed'),
        ]);
    }
}
