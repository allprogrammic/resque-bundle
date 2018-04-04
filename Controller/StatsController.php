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

class StatsController extends Controller
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
        $redis = $this->get('resque')->getBackend();

        return $this->render('AllProgrammicResqueBundle:stats:index.html.twig', [
            'infos' => $redis->info(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoAction()
    {
        return $this->render('AllProgrammicResqueBundle:stats:info.html.twig', [
            'infos' => $this->get('resque')->getInfos()
        ]);
    }
}
