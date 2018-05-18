<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Date;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 *  @Security("has_role('ROLE_USER')")
 */
class MainController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Session $session)
    {
        if (!$session->get('chosenDate')) {
            $currentDate = new \DateTime('now');
            $currentYear = $currentDate->format('Y');
            $currentMonth = $currentDate->format('n');
            $dateMonthName = $currentDate->format('F');

            $workingDate = $this->getDoctrine()->getRepository(Date::class)->findOneBy([
                'user' => $this->getUser(),
                'year' => $currentYear,
                'month' => $currentMonth
            ]);

            if (!$workingDate) {
                $workingDate = $this->getDoctrine()->getRepository(Date::class)->findOneBy(['user' => $this->getUser()],
                    ['year' => 'ASC', 'month' => 'ASC']);
            }

            if (!$workingDate) {
                $newDate = new Date();
                $newDate->setUser($this->getUser());
                $newDate->setYear($currentYear);
                $newDate->setMonth($currentMonth);
                $newDate->setMonthName($dateMonthName);

                $em = $this->getDoctrine()->getManager();
                $em->persist($newDate);
                $em->flush();

                $session->set('chosenDate', ['year' => $currentYear, 'month' => $currentMonth, 'monthName' => $dateMonthName]);
                return $this->redirectToRoute('dashboardMain');
            }

            $session->set('chosenDate', ['year' => $workingDate->getYear(), 'month' => $workingDate->getMonth(), 'monthName' => $workingDate->getMonthName()]);
            return $this->redirectToRoute('dashboardMain');

        }
        return $this->redirectToRoute('dashboardMain');
    }

    /**
     * @Route("/clear", name="clear")
     */
    public function clearAction(Session $session)
    {
        $session->remove('chosenDate');
        return $this->redirectToRoute('homepage');
    }

}
