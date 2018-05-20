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
use Symfony\Component\HttpFoundation\Session\Session;

/**
 *  @Security("has_role('ROLE_USER')")
 */
class TransactionsController extends Controller
{
    /**
     * @Route("/transactions/{year}/{month}/{monthName}", name="transactions")
     */
    public function transactionsAction(Session $session, $year, $month, $monthName)
    {
        $session->set('chosenDate', ['year' => $year, 'month' => $month, 'monthName' => $monthName]);
        return $this->redirectToRoute('transactionsMain');
    }

    /**
     * @Route("/transactions", name="transactionsMain")
     */
    public function transactionsMainAction(Request $request, Session $session)
    {
        $sessionDate = $session->get('chosenDate');

        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);
        $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);

        $userDates = $this->getUser()->getDates();
        $chosenDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);

        $transactions = $transactionRepo->findBy(['user' => $this->getUser(), 'workingDate' => $chosenDate], ['date' => 'DESC']);
        $files = $this->getDoctrine()->getRepository(File::class)->findBy(['user' => $this->getUser(), 'date' => $chosenDate, 'parsed' => null ], ['added' => 'DESC']);
        $budgets = $budgetRepo->findBy(['date' => $chosenDate]);

        $alert = $session->get('transaction-alert');
        $session->remove('transaction-alert');
        if (!$alert) $alert = null;

        return $this->render('transactions/transactions.html.twig', [
            'year' => $sessionDate['year'],
            'month' => $sessionDate['year'],
            'monthName' => $sessionDate['monthName'],
            'userDates' => $userDates,
            'files' => $files,
            'transactions' => $transactions,
            'budgets' => $budgets,
            'alert' => $alert
        ]);
    }

    /**
     * @Route("/transactions/add-cash", name="transactionsAddCash", methods={"post"})
     */
    public function transactionsAddAction(Request $request, Session $session)
    {
        $session->set('transaction-alert', ['danger', 'Error!']);

        if ($request->get('addCash')) {
            $sessionDate = $session->get('chosenDate');

            $dateRepo = $this->getDoctrine()->getRepository(Date::class);
            $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);

            $chosenDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);
            $transactionDate = \DateTime::createFromFormat('m/d/Y', $request->get('date'));

            $em = $this->getDoctrine()->getManager();
            $newTransaction = new Transaction();
            $newTransaction->setUser($this->getUser());
            $newTransaction->setWorkingDate($chosenDate);
            $newTransaction->setDate($transactionDate->format('Y-m-d'));
            $newTransaction->setType('Cash transaction');
            $newTransaction->setRecipient($request->get('recipient'));
            $newTransaction->setDescription($request->get('description'));
            $newTransaction->setAmount('-'.$request->get('amount'));
            $newTransaction->setBudget($budgetRepo->find($request->get('budget')));
            $em->persist($newTransaction);
            $em->flush();

            $session->set('transaction-alert', ['info', 'New cash transaction added!']);
        }

        return $this->redirectToRoute('transactionsMain');

    }

    /**
     * @Route("/transactions/edit", name="transactionsEdit", methods={"post"})
     */
    public function transactionsEditAction(Request $request, Session $session)
    {
        $session->set('transaction-alert', ['danger', 'Error!']);

        if ($request->get('edit')) {
            $sessionDate = $session->get('chosenDate');

            $dateRepo = $this->getDoctrine()->getRepository(Date::class);
            $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);
            $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);

            $chosenDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);
            $transaction = $transactionRepo->find($request->get('transactionId'));

            if ($transaction) {
                $em = $this->getDoctrine()->getManager();
                $transaction->setDate($request->get('date'));
                $transaction->setType($request->get('type'));
                $transaction->setAmount($request->get('amount'));
                $transaction->setRecipient($request->get('recipient'));
                $transaction->setDescription($request->get('description'));
                $transaction->setBudget($budgetRepo->findOneBy(['date' => $chosenDate, 'name' => $request->get('budget')]));
                $em->persist($transaction);
                $em->flush();

                $session->set('transaction-alert', ['info', 'Transaction edited!']);
            }
        }

        return $this->redirectToRoute('transactionsMain');
    }

    /**
     * @Route("/transactions/delete", name="transactionsDelete")
     */
    public function transactionsDeleteAction(Request $request, Session $session)
    {
        $session->set('transaction-alert', ['danger', 'Error!']);

        if ($request->get('delete')) {
            $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);
            $transaction = $transactionRepo->find($request->get('transactionId'));

            if ($transaction) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($transaction);
                $em->flush();

                $session->set('transaction-alert', ['info', 'Transaction deleted!']);
            }
        }

        return $this->redirectToRoute('transactionsMain');
    }

    /**
     * @Route("/transactions/parser", name="transactionsParser", methods={"POST"})
     */
    public function transactionsParserAction(Request $request, Session $session)
    {
        $sessionDate = $session->get('chosenDate');

        $dateRepo = $this->getDoctrine()->getRepository(Date::class);
        $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);
        $fileRepo = $this->getDoctrine()->getRepository(File::class);

        $userDates = $this->getUser()->getDates();
        $workingDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);
        $dateBudgets = $budgetRepo->findBy(['date' => $workingDate]);

        $fileId = $request->get('fileId');
        $file = $fileRepo->find($fileId);
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

        $budgets = [];
        foreach ($dateBudgets as $dateBudget) {
            $budgets[] = $dateBudget->getName();
        }

        return $this->render('transactions/transaction_parser.html.twig', [
            'year' => $sessionDate['year'],
            'month' => $sessionDate['month'],
            'monthName' => $sessionDate['monthName'],
            'userDates' => $userDates,
            'file' => $file,
            'dates' => $dates,
            'types' => $types,
            'recipients' => $recipients,
            'amounts' => $amounts,
            'budgets' => $budgets
        ]);
    }

    /**
     * @Route("/transactions/parsed", name="transactionsParsed", methods={"POST"})
     */
    public function transactionsParsedAction(Request $request, Session $session)
    {
        if ($request->get('parse')) {
            $sessionDate = $session->get('chosenDate');

            $dateRepo = $this->getDoctrine()->getRepository(Date::class);
            $budgetRepo = $this->getDoctrine()->getRepository(Budget::class);
            $transactionRepo = $this->getDoctrine()->getRepository(Transaction::class);
            $fileRepo = $this->getDoctrine()->getRepository(File::class);

            $chosenDate = $dateRepo->findOneBy(['user' => $this->getUser(), 'year' => $sessionDate['year'], 'month' => $sessionDate['month']]);

            $budgets = $request->get('budget');
            $dates = $request->get('date');
            $types = $request->get('type');
            $amounts = $request->get('amount');
            $recipients = $request->get('recipient');
            $descriptions = $request->get('description');

            $em = $this->getDoctrine()->getManager();

            for ($i = 0; $i < count($request->get('id')); $i++) {
                if ($budgets[$i]) {
                    $budget = $budgetRepo->findOneBy(['date' => $chosenDate, 'name' => $budgets[$i]]);
                }

                $transaction = new Transaction();
                $transaction->setUser($this->getUser());
                $transaction->setWorkingDate($chosenDate);
                if ($budget) {
                    $transaction->setBudget($budget);
                }
                $transaction->setDate($dates[$i]);
                $transaction->setType($types[$i]);
                $transaction->setAmount($amounts[$i]);
                $transaction->setRecipient($recipients[$i]);
                $transaction->setDescription($descriptions[$i]);

                if ($transactionRepo->findOneBy(['user' => $this->getUser(), 'workingDate' => $chosenDate, 'date' => $dates[$i], 'type' => $types[$i], 'amount' => $amounts[$i], 'recipient' => $recipients[$i]]) == null) {

                    $em->persist($transaction);
                    $em->flush();

                    $session->set('transaction-alert', ['info', 'Transactions added!']);

                }
            }

            $file = $fileRepo->find($request->get('fileId'));
            $file->setParsed('yes');
            $em->persist($file);
            $em->flush();

        }

        return $this->redirectToRoute('transactionsMain');
    }

}
