<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="t_exchange_code")
 */
class ExchangeCode
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(name="code",type="string", length=40)
     */
    protected $code;
    /**
     * @ORM\Column(name="is_used",type="boolean")
     */
    protected $isUsed=0;
    /**
     * @ORM\OneToOne(targetEntity="Form", mappedBy="code")
     */
    protected $form;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return ExchangeCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set isUsed
     *
     * @param boolean $isUsed
     * @return ExchangeCode
     */
    public function setIsUsed($isUsed)
    {
        $this->isUsed = $isUsed;

        return $this;
    }

    /**
     * Get isUsed
     *
     * @return boolean 
     */
    public function getIsUsed()
    {
        return $this->isUsed;
    }

    /**
     * Set form
     *
     * @param \AppBundle\Entity\Form $form
     * @return ExchangeCode
     */
    public function setForm(\AppBundle\Entity\Form $form = null)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form
     *
     * @return \AppBundle\Entity\Form 
     */
    public function getForm()
    {
        return $this->form;
    }
}
