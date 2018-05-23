<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Budget;
use AppBundle\Entity\Date;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 *  @Security("has_role('ROLE_USER')")
 */
class ApiBudgetController extends Controller
{

    /**
     * @Route("/budget", methods={"POST"})
     */
    public function apiBudgetPostAction(Request $request)
    {
        $year = $request->request->get('year');
        $month = $request->request->get('month');
        $budgetName = $request->request->get('name');
        $budgetValue = $request->request->get('value');
        $date = $this->getDoctrine()->getRepository(Date::class)->findOneBy(['user' => $this->getUser(), 'year' => $year, 'month' => $month]);

        if ($date) {
            $budget = new Budget();
            $budget->setDate($date);
            $budget->setName($budgetName);
            $budget->setValue($budgetValue);
            $em = $this->getDoctrine()->getManager();
            $em->persist($budget);
            $em->flush();
        }

        return $this->json([$year, $month, $budgetName, $budgetValue]);
    }
}
