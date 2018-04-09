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

use AllProgrammic\Bundle\ResqueBundle\Form\RecurringJobType;
use AllProgrammic\Bundle\ResqueBundle\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RecurringController extends Controller
{
    public function indexAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        // Create new paginator
        $pager = new Paginator($this->get('resque')->getRecurring());
        $jobs  = $pager
            ->setMaxPerPage(15)
            ->setCurrentPage($page)
            ->getCurrentPageResults();

        return $this->render('AllProgrammicResqueBundle:recurring:index.html.twig', [
            'jobs'  => $jobs,
            'pager' => $pager
        ]);
    }

    public function insertAction(Request $request)
    {
        $form = $this->createCreateForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data['args'] = json_decode($data['args'], true);

            // Add recurring job
            $this->get('resque')->insertRecurringJobs($data);

            return $this->redirectToRoute('resque_recurring');
        }

        return $this->render('AllProgrammicResqueBundle:recurring:new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function updateAction(Request $request, $id)
    {
        if (!$data = $this->get('resque')->getRecurringJob($id)) {
            throw new NotFoundHttpException('Unable to find recurring job');
        }

        $data = json_decode($data, true);
        $data['args'] = json_encode($data['args']);

        $form = $this->createCreateForm($data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data['args'] = json_decode($data['args'], true);

            // Add recurring job
            $this->get('resque')->updateRecurringJobs($id, $data);

            return $this->redirectToRoute('resque_recurring');
        }

        return $this->render('AllProgrammicResqueBundle:recurring:update.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function removeAction(Request $request, $id)
    {
        if (!$this->get('resque')->getRecurringJob($id)) {
            throw new NotFoundHttpException('Unable to find recurring job');
        }

        $this->get('resque')->removeRecurringJobs($id);

        return $this->redirectToRoute('resque_recurring');
    }

    /**
     * Create form
     *
     * @return mixed|\Symfony\Component\Form\FormInterface
     */
    public function createCreateForm($data = null)
    {
        $form = $this->createForm(RecurringJobType::class, $data);

        $form->add('submit', SubmitType::class, [
            'label' => 'Save',
            'attr' => [
                'class' => 'btn-danger'
            ]
        ]);

        return $form;
    }
}
