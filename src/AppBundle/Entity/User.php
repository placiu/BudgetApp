<?php
namespace AppBundle\Entity;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="user")
     */
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity="Date", mappedBy="user")
     */
    private $dates;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="user")
     */
    private $files;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Add date
     *
     * @param \AppBundle\Entity\Date $date
     *
     * @return User
     */
    public function addDate(\AppBundle\Entity\Date $date)
    {
        $this->dates[] = $date;

        return $this;
    }

    /**
     * Remove date
     *
     * @param \AppBundle\Entity\Date $date
     */
    public function removeDate(\AppBundle\Entity\Date $date)
    {
        $this->dates->removeElement($date);
    }

    /**
     * Get dates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDates()
    {
        return $this->dates;
    }

    /**
     * Add file
     *
     * @param \AppBundle\Entity\File $file
     *
     * @return User
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
     * Add transaction
     *
     * @param \AppBundle\Entity\Transaction $transaction
     *
     * @return User
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
