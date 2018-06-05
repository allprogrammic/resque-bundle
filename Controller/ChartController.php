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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChartController extends Controller
{
    /**
     * Index action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('AllProgrammicResqueBundle:chart:default.html.twig');
    }

    /**
     * Failures action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function failuresAction()
    {
        $data = $this->get('resque.charts')->getFailure()->peek(0, 0);
        $data = $this->getData($data);

        return new JsonResponse($data);
    }

    /**
     * Processed action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function processedAction()
    {
        $data = $this->get('resque.charts')->getProcess()->peek(0, 0);
        $data = $this->getData($data);

        return new JsonResponse($data);
    }

    /**
     * @param $data
     * @param int $interval
     *
     * @return array
     */
    public function getData($data, $interval = 7, $format = 'm-d')
    {
        $data   = array_values($data);
        $diff   = strtotime('now');
        $result = [];

        array_multisort($data, SORT_ASC);

        for ($i = 0; $i < $interval; $i++) {
            if ($i !== 0) {
                $diff = strtotime(sprintf('-%s days', $i));
            }

            $result[date($format, $diff)] = 0;
        }

        foreach ($data as $key => $value) {
            if (!isset($value['date'])) {
                continue;
            }

            $date = date('Y-m-d', $value['date']);

            if (strtotime($date) < strtotime(sprintf('-%s days', $interval))) {
                continue;
            }

            $count = $value['count'];
            $result[date($format, strtotime($date))] = $count;
        }

        return array_reverse($result);
    }
}
