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

use AllProgrammic\Bundle\ResqueBundle\Form\CleanerType;
use AllProgrammic\Bundle\ResqueBundle\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class CleanerController extends Controller
{
    /**
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        $page = $request->query->get('page', 1);

        // Create new paginator
        $pager = new Paginator($this->get('resque')->getCleaner());
        $tasks = $pager
            ->setMaxPerPage(15)
            ->setCurrentPage($page)
            ->getCurrentPageResults();

        return $this->render('@AllProgrammicResque/cleaner/index.html.twig', [
            'tasks' => $tasks,
            'pager' => $pager
        ]);
    }

    /**
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
            $this->get('resque')->insertCleanerTask($data);

            return $this->redirectToRoute('resque_cleaner');
        }

        return $this->render('@AllProgrammicResque/cleaner/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function editAction(Request $request, $id)
    {
        if (!$data = $this->get('resque')->getCleanerTask($id)) {
            throw new NotFoundHttpException('Unable to find cleaner task');
        }

        $data = json_decode($data, true);
        $form = $this->createCreateForm($data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Add recurring job
            $this->get('resque')->updateCleanerTask($id, $data);

            return $this->redirectToRoute('resque_cleaner');
        }

        return $this->render('@AllProgrammicResque/cleaner/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction($id)
    {
        if (!$this->get('resque')->getCleanerTask($id)) {
            throw new NotFoundHttpException('Unable to find cleaner task');
        }

        $this->get('resque')->removeCleanerTask($id);

        return $this->redirectToRoute('resque_cleaner');
    }

    /**
     * Create form
     *
     * @return mixed|\Symfony\Component\Form\FormInterface
     */
    public function createCreateForm($data = null)
    {
        $form = $this->createForm(CleanerType::class, $data);

        $form->add('submit', SubmitType::class, [
            'label' => 'Save',
            'attr' => [
                'class' => 'btn-danger'
            ]
        ]);

        return $form;
    }
}