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

/**
 *  @Security("has_role('ROLE_USER')")
 */
class FileController extends Controller
{
    /**
     * @Route("/filemanager", name="filemanagermain")
     */
    public function fileManagerMainAction()
    {
        $date = $this->getDoctrine()->getRepository(Date::class)->findOneBy(['user' => $this->getUser()], ['year' => 'ASC', 'month' => 'ASC']);
        return $this->redirectToRoute('filemanager', ['year' => $date->getYear(), 'month' => $date->getMonth()]);
    }

    /**
     * @Route("/{year}/{month}/filemanager", name="filemanager")
     */
    public function fileManagerAction(Request $request, $year, $month)
    {
        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $workingDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $year, 'month' => $month]);

        $userDates = $dateRepo->findBy(['user' => $this->getUser()], ['year' => 'DESC', 'month' => 'ASC'], 12);

        $upload = $this->createForm(UploadForm::class, new File($this->getUser()));
        $upload->handleRequest($request);

        if ($upload->isSubmitted()) {
            $date = new \DateTime('now');
            $historyFile = $upload->getData();
            $historyFile->setAdded($date);
            $historyFile->setDate($workingDate);
            $em = $this->getDoctrine()->getManager();
            $em->persist($historyFile);
            $em->flush();

            return $this->redirectToRoute('filemanager', ['year' => $year, 'month' => $month]);
        }

        $files = $this->getDoctrine()->getRepository(File::class)->findBy(['user' => $this->getUser(), 'date' => $workingDate ], ['added' => 'DESC']);

        return $this->render('filemanager/filemanager.html.twig', [
            'year' => $year,
            'month' => $month,
            'upload' => $upload->createView(),
            'files' => $files,
            'months' => $userDates
        ]);
    }

    /**
     * @Route("/{year}/{month}/filemanager/delete", name="deleteHistoryFile")
     */
    public function deleteFileAction(Request $request, $year, $month)
    {
        $file = $this->getDoctrine()->getRepository(File::class)->findOneBy(['id' => $request->get('file_id')]);
        if($file) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($file);
            $em->flush();
        }
        return $this->redirectToRoute('filemanager', ['year' => $year, 'month' => $month]);
    }
}
