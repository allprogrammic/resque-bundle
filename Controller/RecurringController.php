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

use AllProgrammic\Bundle\ResqueBundle\Form\ImportType;
use AllProgrammic\Bundle\ResqueBundle\Form\RecurringJobType;
use AllProgrammic\Bundle\ResqueBundle\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;

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

    public function historyAction(Request $request, $id)
    {
        if (!$job = json_decode($this->get('resque')->getRecurringJob($id), true)) {
            throw new NotFoundHttpException('Unable to find recurring job');
        }

        if (!$history = $this->get('resque.history')) {
            throw new NotFoundHttpException('Unable to find recurring job history');
        }

        $page = $request->query->get('page', 1);
        $backend = $this->get('resque.history');
        $backend->setName($job['name']);

        // Create new paginator
        $pager = new Paginator($backend);
        $jobs  = $pager
            ->setMaxPerPage(15)
            ->setCurrentPage($page)
            ->getCurrentPageResults();

        return $this->render('AllProgrammicResqueBundle:recurring:history.html.twig', [
            'job'   => $job,
            'id'    => $id,
            'jobs'  => $jobs,
            'pager' => $pager,
        ]);
    }

    public function insertAction(Request $request)
    {
        $form = $this->createCreateForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

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
        $form = $this->createCreateForm($data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

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
     * Export action
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exportAction(Request $request)
    {
        $data = $this->get('resque')->getRecurring()->peek(0, 0);

        // Provide a name for your file with extension
        $filename = sprintf('%s.yml', time());

        $fileContent = Yaml::dump($data);

        // Return a response with a specific content
        $response = new Response($fileContent);

        // Create the disposition of the file
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        // Set the content disposition
        $response->headers->set('Content-Disposition', $disposition);

        // Dispatch request
        return $response;

    }

    /**
     * Import action
     *
     * @param Request $request
     *
     * @return Response
     */
    public function importAction(Request $request)
    {
        $form = $this->createForm(ImportType::class);
        $form->add('submit', SubmitType::class, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->getData()['file'];
            $path = $file->getPathname();
            $data = Yaml::parse(file_get_contents($path));

            foreach ($data as $key => $value) {
                $this->get('resque')->insertRecurringJobs($value);
            }

            return $this->redirectToRoute('resque_recurring');
        }

        return $this->render('AllProgrammicResqueBundle:recurring:import.html.twig', [
            'form' => $form->createView()
        ]);
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
