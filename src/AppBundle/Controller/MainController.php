<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Date;
use AppBundle\Entity\Transaction;
use AppBundle\Form\DateForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 *  @Security("has_role('ROLE_USER')")
 */
class MainController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $workingDate = $this->getDoctrine()->getRepository(Date::class)->findOneBy(['user' => $this->getUser()], ['year' => 'ASC', 'month' => 'ASC']);
        if (!$workingDate) {
            $date = new \DateTime('now');
            $dateYear = $date->format('Y');
            $dateMonth = $date->format('n');
            $dateMonthName = $date->format('F');

            $newDate = new Date();
            $newDate->setUser($this->getUser());
            $newDate->setYear($dateYear);
            $newDate->setMonth($dateMonth);
            $newDate->setMonthName($dateMonthName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newDate);
            $em->flush();



            return $this->redirectToRoute('dashboard', ['year' => $dateYear, 'month' => $dateMonth]);
        } else {
            return $this->redirectToRoute('dashboard', ['year' => $workingDate->getYear(), 'month' => $workingDate->getMonth()]);
        }

    }
}
