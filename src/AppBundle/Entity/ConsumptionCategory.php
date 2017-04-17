<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ConsumptionCategory
 *
 * @ORM\Table(name="consumption_category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ConsumptionCategoryRepository")
 */
class ConsumptionCategory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", options={"default":0})
     */
    private $is_user = 0;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Consumption",
     *     mappedBy="category"
     * )
     */
    private $consumption;

    /**
     * ConsumptionCategory constructor.
     */
    public function __construct()
    {
        $this->created= new \DateTime();
        $this->updated= new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsUser()
    {
        return $this->is_user;
    }

    /**
     * @param $is_user
     * @return $this
     */
    public function setIsUser($is_user)
    {
        $this->is_user = $is_user;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updated= new \DateTime();
    }
}
