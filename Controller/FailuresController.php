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

class FailuresController extends Controller
{
    public function indexAction(Request $request)
    {
        $failedSize = $this->get('resque')->getFailure()->count();
        $jobPerPage = 25;
        $offset = $request->query->get('start', 0);

        if ($offset > $failedSize) {
            $offset = $failedSize - $jobPerPage;
        }

        $count = $jobPerPage;
        $maxOffset = $offset + $count;
        if ($maxOffset > $failedSize) {
            $count -= $maxOffset - $failedSize;
        }

        $jobs = $this->get('resque')->getFailure()->peek(
            $offset,
            $count
        );

        return $this->render('AllProgrammicResqueBundle:failures:index.html.twig', [
            'failure_size' =>  $failedSize,
            'failure_start_at' => $offset + 1,
            'failure_end_at' => $offset + $count,
            'jobs' => $jobs,
        ]);
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

}
