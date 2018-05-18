<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Budget;
use AppBundle\Entity\Date;
use AppBundle\Entity\File;
use AppBundle\Entity\Transaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *  @Security("has_role('ROLE_USER')")
 */
class TransactionsController extends Controller
{
    /**
     * @Route("/transactions", name="transactionsmain")
     */
    public function transactionsMainAction()
    {
        $date = $this->getDoctrine()->getRepository(Date::class)->findOneBy(['user' => $this->getUser()], ['year' => 'ASC', 'month' => 'ASC']);
        return $this->redirectToRoute('transactions', ['year' => $date->getYear(), 'month' => $date->getMonth()]);
    }

    /**
     * @Route("/{year}/{month}/transactions", name="transactions")
     */
    public function transactionsAction(Request $request, $year, $month)
    {
        $alert = [];

        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);
        $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);

        $userDates = $dateRepo->findBy(['user' => $this->getUser()]);
        $workingDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $year, 'month' => $month]);
        $workingMonthName = $workingDate->getMonthName();

        $files = $this->getDoctrine()->getRepository(File::class)->findBy(['user' => $this->getUser(), 'date' => $workingDate, 'parsed' => null ], ['added' => 'DESC']);
        $transactions = $transactionRepo->findBy(['user' => $this->getUser(), 'workingDate' => $workingDate], ['date' => 'DESC']);

        if ($request->get('addcash')) {
            $transactionDate = \DateTime::createFromFormat('m/d/Y', $request->get('date'));

            $em = $this->getDoctrine()->getManager();
            $newTransaction = new Transaction();
            $newTransaction->setUser($this->getUser());
            $newTransaction->setWorkingDate($workingDate);
            $newTransaction->setDate($transactionDate->format('Y-m-d'));
            $newTransaction->setType('Cash transaction');
            $newTransaction->setRecipient($request->get('recipient'));
            $newTransaction->setDescription($request->get('description'));
            $newTransaction->setAmount('-'.$request->get('amount'));
            $newTransaction->setBudget($budgetRepo->find($request->get('budget')));
            $em->persist($newTransaction);
            $em->flush();

            $transactions = $transactionRepo->findBy(['user' => $this->getUser(), 'workingDate' => $workingDate], ['date' => 'DESC']);
            $alert = ['info', 'New cash transaction added!'];
        }

        if ($request->get('edit')) {

            $transaction = $transactionRepo->find($request->get('transactionId'));
            if($transaction) {
                $em = $this->getDoctrine()->getManager();
                $transaction->setDate($request->get('date'));
                $transaction->setType($request->get('type'));
                $transaction->setAmount($request->get('amount'));
                $transaction->setRecipient($request->get('recipient'));
                $transaction->setDescription($request->get('description'));
                $transaction->setBudget($budgetRepo->findOneBy(['date' => $workingDate, 'name' => $request->get('budget')]));

                $em->persist($transaction);
                $em->flush();

                $transactions = $transactionRepo->findBy(['user' => $this->getUser(), 'workingDate' => $workingDate], ['date' => 'DESC']);
                $alert = ['info', 'Transaction edited!'];
            }

        }

        if ($request->get('delete')) {
            $transaction = $transactionRepo->find($request->get('transactionId'));
            if($transaction) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($transaction);
                $em->flush();

                $transactions = $transactionRepo->findBy(['user' => $this->getUser(), 'workingDate' => $workingDate, ['date' => 'DESC']]);
                $alert = ['info', 'Transaction deleted!'];
            }
        }

        $budgets = $budgetRepo->findBy(['date' => $workingDate]);

        return $this->render('transactions/transactions.html.twig', [
            'year' => $year,
            'month' => $month,
            'monthName' => $workingMonthName,
            'userDates' => $userDates,
            'files' => $files,
            'transactions' => $transactions,
            'budgets' => $budgets,
            'alert' => $alert
        ]);
    }

    /**
     * @Route("/{year}/{month}/transactions/parser", name="transactionsparser", methods={"POST"})
     */
    public function transactionsParserAction(Request $request, $year, $month)
    {
        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $userDates = $this->getUser()->getDates();
        $workingDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $year, 'month' => $month]);
        $workingMonthName = $workingDate->getMonthName();

        $fileId = $request->get('id');
        $file = $this->getDoctrine()->getRepository(File::class)->find($fileId);
        $path = File::FILE_PATH;

        $dom = new \DOMDocument();
        $dom->loadHTMLFile($path.'/'.$file->getFile());
        $tds = $dom->getElementsByTagName('td');

        $tdValues = [];
        foreach ($tds as $td) {
            $tdValues[] = $td->nodeValue;
        }

        $dates = [];
        $types = [];
        $recipients = [];
        $amounts = [];

        for ($i = 12; $i < sizeof($tdValues); $i += 11) {
            $dates[] = $tdValues[$i];
            $type = $tdValues[$i + 2];
            $type = ucfirst(mb_strtolower(preg_replace('!\s+!', ' ', $type)));
            $types[] = $type;
            $recipient = $tdValues[$i + 5];
            $recipient = ucfirst(mb_strtolower(preg_replace('!\s+!', ' ', $recipient)));
            $recipients[] = $recipient;
            if (!preg_match('~[0-9]~', $tdValues[$i + 6]) === true) {
                $amounts[] = $tdValues[$i + 7];
            } else {
                $amounts[] = $tdValues[$i + 6];
            }
        }

        $dateBudgets = $this->getDoctrine()->getRepository(Budget::class)->findBy(['date' => $workingDate]);
        $budgets = [];
        foreach ($dateBudgets as $dateBudget) {
            $budgets[] = $dateBudget->getName();
        }

        return $this->render('transactions/transaction_parser.html.twig', [
            'year' => $year,
            'month' => $month,
            'monthName' => $workingMonthName,
            'userDates' => $userDates,
            'file' => $file->getFile(),
            'dates' => $dates,
            'types' => $types,
            'recipients' => $recipients,
            'amounts' => $amounts,
            'budgets' => $budgets
        ]);
    }

    /**
     * @Route("/{year}/{month}/transactions/parsed", name="transactionsparsed", methods={"POST"})
     */
    public function transactionsParsedAction(Request $request, $year, $month)
    {
        if ($request->get('parse')) {

            $dateRepo = $this->getDoctrine()->getRepository(Date::class);
            $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);
            $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);
            $workingDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $year, 'month' => $month]);

            $budgets = $request->get('budget');

            for ($i = 0; $i < count($request->get('id')); $i++) {
                if ($budgets[$i]) {
                    $budget = $budgetRepo->findOneBy(['date' => $workingDate, 'name' => $budgets[$i]]);
                }
                $dates = $request->get('date');
                $types = $request->get('type');
                $amounts = $request->get('amount');
                $recipients = $request->get('recipient');
                $descriptions = $request->get('description');

                $transaction = new Transaction();
                $transaction->setUser($this->getUser());
                $transaction->setWorkingDate($workingDate);
                if ($budget) {
                    $transaction->setBudget($budget);
                }
                $transaction->setDate($dates[$i]);
                $transaction->setType($types[$i]);
                $transaction->setAmount($amounts[$i]);
                $transaction->setRecipient($recipients[$i]);
                $transaction->setDescription($descriptions[$i]);

                if ($transactionRepo->findOneBy(['user' => $this->getUser(), 'workingDate' => $workingDate, 'date' => $dates[$i], 'type' => $types[$i], 'amount' => $amounts[$i], 'recipient' => $recipients[$i]]) == null) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($transaction);
                    $em->flush();
                }

            }
        }

        return $this->redirectToRoute('transactions', ['year' => $year, 'month' => $month]);
    }

}
