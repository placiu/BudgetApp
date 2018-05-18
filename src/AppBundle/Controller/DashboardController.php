<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Date;
use AppBundle\Entity\Transaction;
use AppBundle\Form\DateForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 *  @Security("has_role('ROLE_USER')")
 */
class DashboardController extends Controller
{
    /**
     * @Route("/dashboard/{year}/{month}/{monthName}", name="dashboard")
     */
    public function dashboardAction(Session $session, $year, $month, $monthName)
    {
        $session->set('chosenDate', ['year' => $year, 'month' => $month, 'monthName' => $monthName]);
        return $this->redirectToRoute('dashboardMain');
    }

    /**
     * @Route("/dashboard", name="dashboardMain")
     */
    public function dashboardMainAction(Session $session)
    {
        $sessionDate = $session->get('chosenDate');

        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);

        $userDates = $this->getUser()->getDates();
        $chosenDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);
        $transactions = $transactionRepo->findBy(['user' => $this->getUser(), 'workingDate' => $chosenDate], ['date' => 'ASC']);

        $totalIncome = 0;
        $totalOutcome = 0;
        $incomeTransactions = [];
        $outcomeTransactions = [];

        foreach ($transactions as $transaction) {
            $transactionAmount = $transaction->getAmount();
            if ($transactionAmount < 0) {
                $totalOutcome += $transactionAmount;
                $outcomeTransactions[] = $transaction;
            } else {
                $totalIncome += $transactionAmount;
                $incomeTransactions[] = $transaction;
            }
        }

        $outcomeTransactions = array_reverse($outcomeTransactions);
        $incomeTransactions = array_reverse($incomeTransactions);

        $transactionsByDay = [];
        for ($x = 0; $x < cal_days_in_month(CAL_GREGORIAN, $sessionDate['month'], $sessionDate['year']); $x++) {
            $transactionsByDay[$x] = 0;
            foreach ($transactions as $transaction) {
                $transactionDate = $transaction->getDate();
                $transactionDate = new \DateTime($transactionDate);
                $transactionDay = $transactionDate->format('j');
                if ($transactionDay == $x && $transaction->getAmount() < 0) {
                    $transactionsByDay[$x - 1] += abs($transaction->getAmount());
                }
            }
        }

        $newDateForm = $this->createForm(DateForm::class, new Date());

        $alert = $session->get('alert');
        $session->remove('alert');
        if (!$alert) $alert = null;

        return $this->render('dashboard/dashboard.html.twig', [
            'year' => $sessionDate['year'],
            'month' => $sessionDate['month'],
            'monthName' => $sessionDate['monthName'],
            'userDates' => $userDates,
            'incomeTransactions' => $incomeTransactions,
            'outcomeTransactions' => $outcomeTransactions,
            'totalTransactions' => count($transactions),
            'totalOutcome' => abs(round($totalOutcome)),
            'totalIncome' => round($totalIncome),
            'transactionsByDay' => $transactionsByDay,
            'newDateForm' => $newDateForm->createView(),
            'alert' => $alert,
        ]);
    }

    /**
     * @Route("/dashboard/new-date", name="dashboardnewdate", methods={"post"})
     */
    public function dashboardNewDateAction(Request $request, Session $session)
    {
        $session->set('alert', ['danger', 'Error!']);
        $dateRepo = $this->getDoctrine()->getRepository(Date::class);

        $newDateForm = $this->createForm(DateForm::class, new Date());
        $newDateForm->handleRequest($request);

        if ($newDateForm->isSubmitted()) {
            $newMonth = $newDateForm->getData();

            if (!$dateRepo->findBy(['year' => $newMonth->getYear(), 'month' => $newMonth->getMonth()])) {
                $newMonth->setUser($this->getUser());
                $newMonthNumber = \DateTime::createFromFormat('m', $newMonth->getMonth());
                $newMonthName = $newMonthNumber->format('F');
                $newMonth->setMonthName($newMonthName);
                $em = $this->getDoctrine()->getManager();
                $em->persist($newMonth);
                $em->flush();

                $session->set('alert', ['info', 'New date created!']);

            } else {
                $session->set('alert', ['danger', 'This date already exists!']);
            }
        }

        return $this->redirectToRoute('dashboardMain');

    }

}
