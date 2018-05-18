<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Date;
use AppBundle\Entity\Transaction;
use AppBundle\Form\DateForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 *  @Security("has_role('ROLE_USER')")
 */
class DashboardController extends Controller
{
    /**
     * @Route("/dashboard", name="dashboardmain")
     */
    public function dashboardMainAction()
    {
        $date = $this->getDoctrine()->getRepository(Date::class)->findOneBy(['user' => $this->getUser()], ['year' => 'ASC', 'month' => 'ASC']);
        return $this->redirectToRoute('dashboard', ['year' => $date->getYear(), 'month' => $date->getMonth()]);
    }

    /**
     * @Route("/{year}/{month}/dashboard", name="dashboard")
     */
    public function dashboardAction(Request $request, $year, $month)
    {
        $alert = [];

        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);

        $userDates = $this->getUser()->getDates();
        $workingDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $year, 'month' => $month]);
        $workingMonthName = $workingDate->getMonthName();

        $transactions = $transactionRepo->findBy(['user' => $this->getUser(), 'workingDate' => $workingDate], ['date' => 'ASC']);

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

        $transactionsByDay = [];
        for ($x = 0; $x < cal_days_in_month(CAL_GREGORIAN, $month, $year); $x++) {
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

                $dateRepo = $this->getDoctrine()->getManager()->getRepository(Date::class);
                $userDates = $dateRepo->findBy(['user' => $this->getUser()]);

                $alert = ['info', 'New month created!'];
            } else {
                $alert = ['danger', 'This date already exists!'];
            }
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'year' => $year,
            'month' => $month,
            'monthName' => $workingMonthName,
            'userDates' => $userDates,
            'totalOutcome' => abs(round($totalOutcome)),
            'totalIncome' => round($totalIncome),
            'incomeTransactions' => $incomeTransactions,
            'outcomeTransactions' => $outcomeTransactions,
            'transactionsByDay' => $transactionsByDay,
            'totalTransactions' => count($transactions),
            'newDateForm' => $newDateForm->createView(),
            'alert' => $alert,
        ]);
    }
}
