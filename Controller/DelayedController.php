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

use AllProgrammic\Bundle\ResqueBundle\Pagination\Paginator;
use AllProgrammic\Component\Resque\Engine;
use AllProgrammic\Component\Resque\Worker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DelayedController extends Controller
{
    /**
     * Index action
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        // Create new paginator
        $pager = new Paginator($this->get('resque')->getDelayed());
        $jobs  = $pager
            ->setMaxPerPage(15)
            ->setCurrentPage($page)
            ->getCurrentPageResults();

        return $this->render('@AllProgrammicResque/delayed/index.html.twig', [
            'jobs'  => $jobs,
            'pager' => $pager
        ]);
    }

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction($id)
    {
        /** @var $resque Engine */
        $resque = $this->get('resque');

        /** @var $job array */
        $job = $resque->getDelayed()->peek($id, 1);

        if (empty($job)) {
            return $this->redirectToRoute('resque_delayed');
        }

        $job = $job[0];

        if (!isset($job['timestamp']) || !isset($job['name'])) {
            return $this->redirectToRoute('resque_delayed');
        }

        $prefix = sprintf('delayed:%s:%s', $job['timestamp'], $job['name']);
        $job = $resque->getBackend()->lIndex($prefix, 0);
        $job = json_decode($job, true);

        $resque->getBackend()->del($prefix);
        $resque->getBackend()->del(sprintf('recurring:%s', $job['name']));

        if (!count($resque->getBackend()->keys(sprintf('delayed:%s:*', $job['timestamp'])))) {
            $resque->getBackend()->zrem('delayed_queue_schedule', $job['timestamp']);
        }

        return $this->redirectToRoute('resque_delayed');
    }
}
