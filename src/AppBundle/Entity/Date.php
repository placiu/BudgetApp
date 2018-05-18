<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Date
 *
 * @ORM\Table(name="date")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DateRepository")
 */
class Date
{
    /**
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="workingDate")
     */
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity="Budget", mappedBy="date")
     */
    private $budgets;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="dates")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="date")
     */
    private $files;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="year", type="integer")
     */
    private $year;

    /**
     * @var int
     *
     * @ORM\Column(name="month", type="integer")
     */
    private $month;

    /**
     * @var string
     *
     * @ORM\Column(name="monthName", type="string", length=255)
     */
    private $monthName;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Date
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set month
     *
     * @param integer $month
     *
     * @return Date
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set monthName
     *
     * @param string $monthName
     *
     * @return Date
     */
    public function setMonthName($monthName)
    {
        $this->monthName = $monthName;

        return $this;
    }

    /**
     * Get monthName
     *
     * @return string
     */
    public function getMonthName()
    {
        return $this->monthName;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Date
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add file
     *
     * @param \AppBundle\Entity\File $file
     *
     * @return Date
     */
    public function addFile(\AppBundle\Entity\File $file)
    {
        $this->files[] = $file;

        return $this;
    }

    /**
     * Remove file
     *
     * @param \AppBundle\Entity\File $file
     */
    public function removeFile(\AppBundle\Entity\File $file)
    {
        $this->files->removeElement($file);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }


    /**
     * Add budget
     *
     * @param \AppBundle\Entity\Budget $budget
     *
     * @return Date
     */
    public function addBudget(\AppBundle\Entity\Budget $budget)
    {
        $this->budgets[] = $budget;

        return $this;
    }

    /**
     * Remove budget
     *
     * @param \AppBundle\Entity\Budget $budget
     */
    public function removeBudget(\AppBundle\Entity\Budget $budget)
    {
        $this->budgets->removeElement($budget);
    }

    /**
     * Get budgets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBudgets()
    {
        return $this->budgets;
    }

    /**
     * Add transaction
     *
     * @param \AppBundle\Entity\Transaction $transaction
     *
     * @return Date
     */
    public function addTransaction(\AppBundle\Entity\Transaction $transaction)
    {
        $this->transactions[] = $transaction;

        return $this;
    }

    /**
     * Remove transaction
     *
     * @param \AppBundle\Entity\Transaction $transaction
     */
    public function removeTransaction(\AppBundle\Entity\Transaction $transaction)
    {
        $this->transactions->removeElement($transaction);
    }

    /**
     * Get transactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
}
