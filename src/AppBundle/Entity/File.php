<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File
 *
 * @ORM\Table(name="file")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FileRepository")
 */
class File
{
    CONST FILE_PATH = 'files';

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="files")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Date", inversedBy="files")
     * @ORM\JoinColumn(name="date_id", referencedColumnName="id")
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string")
     *
     *
     * @Assert\File(
     *     maxSize = "100k",
     *     mimeTypes = {"text/html"},
     *     mimeTypesMessage = "Please upload a valid HTML file"
     * )
     */
    private $file;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="added", type="datetime")
     */
    private $added;

    /**
     * @var string
     *
     * @ORM\Column(name="parsed", type="string", nullable=true)
     */
    private $parsed;

    public function __construct(User $user)
    {
        $this->setUser($user);
    }

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
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        /** @var UploadedFile $file */
//        if ($file !== null) {
//            $date = date('Y-m-d__H:i:s');
//            $fileName = $date . '__' . $this->getUser()->getId() . '.' . $file->guessExtension();
//            $file->move(self::FILE_PATH, $fileName);
//            $this->file = $fileName;
//            return $this;
//        }

        $this->file = $file;

        return $this;
    }

    /**
     * Set added
     *
     * @param \DateTime $added
     *
     * @return File
     */
    public function setAdded($added)
    {
        $this->added = $added;

        return $this;
    }

    /**
     * Get added
     *
     * @return \DateTime
     */
    public function getAdded()
    {
        return $this->added;
    }


    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return File
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
     * Set date
     *
     * @param \AppBundle\Entity\Date $date
     *
     * @return File
     */
    public function setDate(\AppBundle\Entity\Date $date = null)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \AppBundle\Entity\Date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getParsed()
    {
        return $this->parsed;
    }

    /**
     * @param string $parsed
     */
    public function setParsed($parsed)
    {
        $this->parsed = $parsed;
    }


}
