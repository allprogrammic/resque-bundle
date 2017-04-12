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

class WorkingController extends Controller
{
    public function viewAction()
    {
        return $this->render('AllProgrammicResqueBundle:working:view.html.twig');
    }

    public function workingAction()
    {
        $workers = $this->get('resque')->getSupervisor()->all();

        $working_workers = array_map(function (Worker $worker) {
            if ($worker->isIdle()) {
                return null;
            }

            return $worker;
        }, $workers);

        $working_workers = array_combine($workers, $working_workers);

        $working_workers = array_filter($working_workers);

        // TODO : sort working by running at

        return $this->render('AllProgrammicResqueBundle:working:_working.html.twig', [
            'workers' => $workers,
            'working_workers' => $working_workers,
        ]);
    }
}
