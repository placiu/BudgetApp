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
            $uploadFile = $upload->getData();
            $uploadFile->setAdded($date);
            $uploadFile->setDate($chosenDate);
            $em = $this->getDoctrine()->getManager();
            $em->persist($uploadFile);
            $em->flush();

            $session->set('filemanager-alert', ['info', 'New file uploaded!']);
        }

        return $this->redirectToRoute('fileManagerMain');
    }

    /**
     * @Route("/fileManager/upload/demo", name="fileManagerUploadDemo")
     */
    public function fileManagerUploadDemoAction(Request $request, Session $session)
    {
        $sessionDate = $session->get('chosenDate');

        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $fileRepo = $this->getDoctrine()->getRepository(File::class);

        $chosenDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);

        $em = $this->getDoctrine()->getManager();

        // for demo -- demofile1
        $demoFile1 = new File($this->getUser());
        $demoFile1->setDate($chosenDate);
        $demoFile1->setUser($this->getUser());
        $demoFile1->setAdded(new \DateTime('now'));
        $demoFile1->setFile('demofile1.html');

        // for demo -- demofile2
        $demoFile2 = new File($this->getUser());
        $demoFile2->setDate($chosenDate);
        $demoFile2->setUser($this->getUser());
        $demoFile2->setAdded(new \DateTime('now'));
        $demoFile2->setFile('demofile2.html');

        if ($demoFile1Exists = $fileRepo->findOneBy(['file' => 'demofile1.html']) == null || $demoFile2Exists = $fileRepo->findOneBy(['file' => 'demofile2.html']) == null) {
            $em->persist($demoFile1);
            $em->persist($demoFile2);
            $em->flush();
            $session->set('filemanager-alert', ['info', 'Demo files uploaded']);
        } else {
            $session->set('filemanager-alert', ['danger', 'Demo files already exists!']);
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
            //unlink(File::FILE_PATH . '/' . $file->getFile());
            $session->set('filemanager-alert', ['info', 'File deleted!']);
        }

        return $this->redirectToRoute('fileManagerMain');
    }

}
