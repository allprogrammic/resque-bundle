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
use AllProgrammic\Component\Resque\Job;
use AllProgrammic\Component\Resque\Worker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FailuresController extends Controller
{
    public function indexAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        // Create new paginator
        $pager = new Paginator($this->get('resque')->getFailure());
        $jobs = $pager
            ->setMaxPerPage(15)
            ->setCurrentPage($page)
            ->getCurrentPageResults();

        return $this->render('AllProgrammicResqueBundle:failures:index.html.twig', [
            'pager' => $pager,
            'jobs' => $jobs,
        ]);
    }

    public function showAction(Request $request, $id)
    {
        $job = $this->get('resque')->getBackend()->lIndex('failed', $id);
        $job = json_decode($job, true);

        if (!$job) {
            throw new NotFoundHttpException('Unable to find job');
        }

        return $this->render('AllProgrammicResqueBundle:failures:show.html.twig', [
            'id' => $id,
            'job' => $job,
        ]);
    }

    /**
     * Reload action
     *
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function reloadAction(Request $request, $id)
    {
        $this->enqueueFailedJob($id);

        return $this->redirectToRoute('resque_failures');
    }

    /**
     * Remove action
     *
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request, $id)
    {
        $this->removeFailedJob($id);

        return $this->redirectToRoute('resque_failures');
    }

    public function refreshAction(Request $request)
    {
        $i = 0;

        while ($item = $this->get('resque')->getBackend()->lpop('failed')) {
            $this->enqueueFailedJob($i);
            $this->removeFailedJob($i);
            $i++;
        }

        return $this->redirectToRoute('resque_failures');
    }

    /**
     * Clear all failed job action
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function clearAction(Request $request)
    {
        $this->get('resque')->getBackend()->del('failed');
        $this->get('resque')->getBackend()->del('stat:failed');

        return $this->redirectToRoute('resque_failures');
    }

    /**
     * Enqueue failed job action
     *
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function enqueueAction(Request $request, $id)
    {
        $this->enqueueFailedJob($id);
        $this->removeFailedJob($id);

        return $this->redirectToRoute('resque_failures');
    }

    /**
     * Enqueue failed job
     *
     * @param $id
     *
     * @return bool
     */
    public function enqueueFailedJob($id)
    {
        $job = $this->get('resque')->getBackend()->lIndex('failed', $id);

        if (!$job) {
            return false;
        }

        $job = json_decode($job, true);
        $job = new Job($job['queue'], $job['payload']);
        $this->get('resque')->recreateJob($job);
    }

    /**
     * Remove failed job
     *
     * @param $id
     *
     * @return bool
     */
    public function removeFailedJob($id)
    {
        $job = $this->get('resque')->getBackend()->lIndex('failed', $id);

        if (!$job) {
            return false;
        }

        $this->get('resque')->getBackend()->lSet('failed', $id, 'DELETE');
        $this->get('resque')->getBackend()->lRem('failed', $id, 'DELETE');
    }
}
