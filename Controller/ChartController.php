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
        $data = $this->get('resque')->getFailure()->peek(0, 0);
        $data = $this->getData($data);

        return $this->render('AllProgrammicResqueBundle:chart:failures.html.twig', [
            'data' => json_encode($data),
        ]);
    }

    /**
     * Processed action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function processedAction()
    {
        $data = $this->get('resque')->getProcessed()->peek(0, 0);
        $data = $this->getData($data);

        return $this->render('AllProgrammicResqueBundle:chart:processed.html.twig', [
            'data' => json_encode($data),
        ]);
    }

    /**
     * @param $data
     * @param int $interval
     *
     * @return array
     */
    public function getData($data, $interval = 7, $format = 'm-d')
    {
        $diff = strtotime('now');
        $result = [];

        for ($i = 0; $i < $interval; $i++) {
            if ($i !== 0) {
                $diff = strtotime(sprintf('-%s days', $i));
            }

            $result[date($format, $diff)] = 0;
        }

        foreach ($data as $key => $value) {
            if (!isset($value['failed_at']) &&
                !isset($value['processed_at'])) {
                continue;
            }

            if (isset($value['failed_at'])) {
                $date = date('Y-m-d', strtotime($value['failed_at']['date']));
            }

            if (isset($value['processed_at'])) {
                $date = date('Y-m-d', $value['processed_at']);
            }

            if (strtotime($date) <
                strtotime(sprintf('-%s days', $interval))) {
                break;
            }

            $count = 1;

            if (isset($value['processed_at'])) {
                $count = $value['count'];
            }

            $result[date($format, strtotime($date))] += $count;
        }

        return array_reverse($result);
    }
}
