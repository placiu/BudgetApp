<?php

namespace AppBundle\Controller;

use AppBundle\Entity\File;
use AppBundle\Entity\Date;
use AppBundle\Entity\User;
use AppBundle\Form\UploadForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 *  @Security("has_role('ROLE_USER')")
 */
class FileController extends Controller
{
    /**
     * @Route("/fileManager/{year}/{month}/{monthName}", name="fileManager")
     */
    public function fileManagerAction(Session $session, $year, $month, $monthName)
    {
        $session->set('chosenDate', ['year' => $year, 'month' => $month, 'monthName' => $monthName]);
        return $this->redirectToRoute('fileManagerMain');
    }

    /**
     * @Route("/fileManager", name="fileManagerMain")
     */
    public function fileManagerMainAction(Session $session)
    {
        $sessionDate = $session->get('chosenDate');

        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $fileRepo = $this->getDoctrine()->getRepository(File::class);

        $chosenDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);
        $userDates = $this->getUser()->getDates();
        $files = $fileRepo->findBy(['user' => $this->getUser(), 'date' => $chosenDate ], ['added' => 'DESC']);

        $upload = $this->createForm(UploadForm::class, new File($this->getUser()));

        $alert = $session->get('filemanager-alert');
        $session->remove('filemanager-alert');
        if (!$alert) $alert = null;

        return $this->render('filemanager/filemanager.html.twig', [
            'year' => $sessionDate['year'],
            'month' => $sessionDate['month'],
            'monthName' => $sessionDate['monthName'],
            'upload' => $upload->createView(),
            'files' => $files,
            'months' => $userDates,
            'alert' => $alert
        ]);
    }

    /**
     * @Route("/fileManager/upload", name="fileManagerUpload", methods={"post"})
     */
    public function fileManagerUploadAction(Request $request, Session $session)
    {
        $sessionDate = $session->get('chosenDate');

        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $chosenDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);

        $upload = $this->createForm(UploadForm::class, new File($this->getUser()));
        $upload->handleRequest($request);

        if ($upload->isSubmitted()) {
            $date = new \DateTime('now');
            $historyFile = $upload->getData();
            $historyFile->setAdded($date);
            $historyFile->setDate($chosenDate);
            $em = $this->getDoctrine()->getManager();
            $em->persist($historyFile);
            $em->flush();

            $session->set('filemanager-alert', ['info', 'New file uploaded!']);
        }

        return $this->redirectToRoute('fileManagerMain');
    }

    /**
     * @Route("/fileManager/delete", name="fileManagerDelete")
     */
    public function fileManagerDeleteAction(Request $request, Session $session)
    {
        $file = $this->getDoctrine()->getRepository(File::class)->findOneBy(['id' => $request->get('file_id')]);

        if ($file) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($file);
            $em->flush();

            $session->set('filemanager-alert', ['info', 'File deleted!']);
        }

        return $this->redirectToRoute('fileManagerMain');
    }
}
