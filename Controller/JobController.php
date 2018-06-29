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

use AllProgrammic\Bundle\ResqueBundle\Form\JobType;
use AllProgrammic\Component\Resque\Worker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class JobController extends Controller
{
    /**
     * Insert new job
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function insertAction(Request $request)
    {
        $form = $this->createCreateForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->get('resque')->enqueue($data['queue'], $data['class'], json_decode($data['args'], true));

            $this->addFlash('notice', 'The job was successfully created');

            return $this->redirectToRoute('resque_overview');
        }

        return $this->render('@AllProgrammicResque/jobs/insert.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Create form
     *
     * @return mixed|\Symfony\Component\Form\FormInterface
     */
    public function createCreateForm()
    {
        $form = $this->createForm(JobType::class, []);

        $form->add('submit', SubmitType::class, [
            'label' => 'Insert',
            'attr' => [
                'class' => 'btn-danger'
            ]
        ]);

        return $form;
    }
}