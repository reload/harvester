<?php
namespace Harvester\AdminBundle;

use Harvester\FetchBundle\Entity\User;
use Symfony\Component\Form\FormFactoryInterface;

class AdminUserForm
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $form_factory;

    /**
     * @param FormFactoryInterface $form_factory
     */
    public function __construct(FormFactoryInterface $form_factory)
    {
        $this->form_factory = $form_factory;
    }

    /**
     * @param User $user
     * @return \Symfony\Component\Form\Form
     */
    public function createForm(User $user)
    {
        return $this->form_factory->createBuilder('form', $user)
                ->add('workingHours', 'text', array(
                    'attr' => array(
                        'placeholder' => 'Eg. 7.5',
                        'class' => 'form-control',
                    )))
                ->add('password', 'text', array(
                    'attr' => array(
                        'value' => null,
                        'class' => 'form-control',
                    )))
                ->add('save', 'submit', array(
                    'validation_groups' => false,
                    'attr' => array(
                        'class' => 'btn btn-default',
                    )))
                ->getForm();
    }
}