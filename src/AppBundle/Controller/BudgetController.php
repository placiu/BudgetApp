<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Budget;
use AppBundle\Entity\Date;
use AppBundle\Entity\Transaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 *  @Security("has_role('ROLE_USER')")
 */
class BudgetController extends Controller
{
    /**
     * @Route("/budget", name="budgetmain")
     */
    public function budgetMainAction()
    {
        $date = $this->getDoctrine()->getRepository(Date::class)->findOneBy(['user' => $this->getUser()], ['year' => 'ASC', 'month' => 'ASC']);
        return $this->redirectToRoute('budget', ['year' => $date->getYear(), 'month' => $date->getMonth()]);
    }

    /**
     * @Route("/{year}/{month}/budget", name="budget")
     */
    public function budgetAction(Request $request, $year, $month)
    {
        $dateRepo = $this->getDoctrine()->getRepository(Date::class);

        $userDates = $dateRepo->findBy(['user' => $this->getUser()]);
        $currentDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $year, 'month' => $month]);
        $currentMonthName = $currentDate->getMonthName();

        $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);
        $budgetsCollection = $budgetRepo->findBy(['date' => $currentDate]);

        $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);

        $budgets = [];

        if ($budgetsCollection) {

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

            $content = 'budget';
            $alert = '';

        } else {
            $content = 'form';
            $alert = ['info', 'No budgets created for this month!'];
        }

        return $this->render('budget/budget.html.twig', [
            'active' => true,
            'year' => $year,
            'month' => $month,
            'monthName' => $currentMonthName,
            'contentType' => $content,
            'alert' => $alert,
            'userDates' => $userDates,
            'budgets' => $budgets
        ]);
    }

    /**
     * @Route("/{year}/{month}/budget/edit", name="budgetedit", methods={"GET"})
     */
    public function budgetEditAction(Request $request, $year, $month)
    {
        $dateRepo = $this->getDoctrine()->getRepository(Date::class);

        $userDates = $dateRepo->findBy(['user' => $this->getUser()]);
        $currentDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $year, 'month' => $month]);
        $currentMonthName = $currentDate->getMonthName();

        $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);
        $budgetsCollection = $budgetRepo->findBy(['date' => $currentDate]);

        return $this->render('budget/budget_edit.html.twig', [
            'active' => true,
            'year' => $year,
            'month' => $month,
            'monthName' => $currentMonthName,
            'userDates' => $userDates,
            'budgets' => $budgetsCollection
        ]);
    }

    /**
     * @Route("/{year}/{month}/budget/edit", name="budgeteditpost", methods={"POST"})
     */
    public function budgetEditPostAction(Request $request, $year, $month)
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
        }

        return $this->redirectToRoute('budget', ['year' => $year, 'month' => $month]);
    }

}
