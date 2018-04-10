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
        $job = json_decode($this->get('resque')->getBackend()->lIndex('failed', $id), true);

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
        $job = $this->get('resque')->getBackend()->lIndex('failed', $id);

        if ($job) {
            $job = json_decode($job, true);
            $job = new Job($job['queue'], $job['payload']);
            $this->get('resque')->recreateJob($job);
        }

        return $this->redirectToRoute('resque_overview');
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
        $job = $this->get('resque')->getBackend()->lIndex('failed', $id);

        if ($job) {
            $this->get('resque')->getBackend()->lSet('failed', $id, 'DELETE');
            $this->get('resque')->getBackend()->lRem('failed', $id, 'DELETE');
        }

        return $this->redirectToRoute('resque_overview');
    }
}
