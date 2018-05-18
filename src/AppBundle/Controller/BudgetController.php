<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Budget;
use AppBundle\Entity\Date;
use AppBundle\Entity\Transaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 *  @Security("has_role('ROLE_USER')")
 */
class BudgetController extends Controller
{
    /**
     * @Route("/budget/{year}/{month}/{monthName}", name="budget")
     */
    public function budgetAction(Session $session, $year, $month, $monthName)
    {
        $session->set('chosenDate', ['year' => $year, 'month' => $month, 'monthName' => $monthName]);
        return $this->redirectToRoute('budgetMain');
    }

    /**
     * @Route("/budget", name="budgetMain")
     */
    public function budgetMainAction(Session $session)
    {
        $sessionDate = $session->get('chosenDate');

        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);
        $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);

        $userDates = $this->getUser()->getDates();
        $chosenDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);

        $budgetsCollection = $budgetRepo->findBy(['date' => $chosenDate]);

        $budgets = [];

        if ($budgetsCollection) {
            $content = 'budget';
            $budgetsSum = 0;

            foreach ($budgetsCollection as $budgetObject) {
                $budgetsSum += $budgetObject->getValue();
            }

            foreach ($budgetsCollection as $budgetObject) {
                $totalOutcome = 0;
                $totalTransactions = 0;
                $budgetTransactions = $transactionRepo->findBy(['budget' => $budgetObject]);

                foreach ($budgetTransactions as $budgetTransaction) {
                    $totalOutcome += abs($budgetTransaction->getAmount());
                    $totalTransactions += 1;
                }

                $share = (string)round(($budgetObject->getValue() / $budgetsSum) * 100);
                $budgets[] = ['budget' => $budgetObject, 'share' => $share, 'totalOutcome' => $totalOutcome, 'budgetTransactions' => $budgetTransactions, 'totalTransactions' => $totalTransactions];
            }

        } else {
            $content = 'form';
            $session->set('budget-alert', ['info', 'No budgets created for this month!']);
        }

        $alert = $session->get('budget-alert');
        $session->remove('budget-alert');
        if (!$alert) $alert = null;

        return $this->render('budget/budget.html.twig', [
            'active' => true,
            'year' => $sessionDate['year'],
            'month' => $sessionDate['month'],
            'monthName' => $sessionDate['monthName'],
            'contentType' => $content,
            'alert' => $alert,
            'userDates' => $userDates,
            'budgets' => $budgets
        ]);
    }

    /**
     * @Route("/budget/edit", name="budgetEditGet", methods={"GET"})
     */
    public function budgetEditAction(Session $session)
    {
        $sessionDate = $session->get('chosenDate');

        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);

        $userDates = $this->getUser()->getDates();
        $chosenDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);

        $budgetsCollection = $budgetRepo->findBy(['date' => $chosenDate]);

        return $this->render('budget/budget_edit.html.twig', [
            'active' => true,
            'year' => $sessionDate['year'],
            'month' => $sessionDate['month'],
            'monthName' => $sessionDate['monthName'],
            'userDates' => $userDates,
            'budgets' => $budgetsCollection
        ]);
    }

    /**
     * @Route("/budget/edit", name="budgetEditPost", methods={"POST"})
     */
    public function budgetEditPostAction(Request $request, Session $session)
    {
        if ($request->get('update')) {
            $budgets = $this->getDoctrine()->getRepository(Budget::class);

            $ids = $request->get('id');
            $names = $request->get('name');
            $values = $request->get('value');

            foreach ($ids as $id) {
                $em = $this->getDoctrine()->getManager();
                $budget = $budgets->find($id);

                if ($names[$id] === '' || $values[$id] === '') {
                    $em->remove($budget);
                    $em->flush();
                } else {
                    $budget->setName($names[$id]);
                    $budget->setValue($values[$id]);
                    $em->persist($budget);
                    $em->flush();
                }
            }

            $session->set('budget-alert', ['info', 'Budgets updated!']);
        }

        return $this->redirectToRoute('budgetMain');
    }

}
