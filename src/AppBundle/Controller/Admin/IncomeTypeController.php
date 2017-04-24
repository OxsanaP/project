<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\IncomeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Repository\IncomeTypeRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Incometype controller.
 *
 * @Route("admin/incometype")
 */
class IncomeTypeController extends Controller
{
    /**
     * Lists all incomeType entities.
     *
     * @Route("/", name="admin_incometype_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $dql   = "SELECT a FROM AppBundle:IncomeType a";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );




        $incomeTypes = $em->getRepository('AppBundle:IncomeType')->findAll();

        return $this->render('admin/incometype/index.html.twig', array(
            'incomeTypes' => $incomeTypes,
            'pagination' => $pagination
        ));
    }

    /**
     * Creates a new incomeType entity.
     *
     * @Route("/new", name="admin_incometype_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $incomeType = new Incometype();
        $form = $this->createForm('AppBundle\Form\IncomeTypeType', $incomeType);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            try {
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($incomeType);
                    $em->flush();

                    $this->addFlash(
                        'success',
                        'The income was added!'
                    );
                    return $this->redirectToRoute('admin_incometype_index');
                }
                if ($form->getErrors()) {
            $this->addFlash(
                'danger',
                $form->getErrors()
            );
        }
    } catch (\Exception $e) {
        $this->addFlash(
            'danger',
            $e->getMessage()
        );
    }
    }
    return $this->render('admin/incometype/new.html.twig', array(
            'incomeType' => $incomeType,
            'form' => $form->createView(),
        ));
    }

     /**
     * Displays a form to edit an existing incomeType entity.
     *
     * @Route("/{id}/edit", name="admin_incometype_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, IncomeType $incomeType)
    {
        $editForm = $this->createForm('AppBundle\Form\IncomeTypeType', $incomeType);
        $editForm->add('isUser', ChoiceType::class, array(
            'choices' => array('No' => 1, 'Yes' => 0), 'label'=>'Visible for all users',
        ));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()){
            try {
                $this->getDoctrine()->getManager()->flush();
            } catch (\Exception $e) {
                    $this->addFlash(
                        'danger',
                        $e->getMessage()
                    );
                }
            $this->addFlash(
                'success',
                "The income {$incomeType->getName()} has changed."
            );

            return $this->redirectToRoute('admin_incometype_index');
        }

        return $this->render('admin/incometype/edit.html.twig', array(
            'incomeType' => $incomeType,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a incomeType entity.
     *
     * @Route("/{id}/delete", name="admin_incometype_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request)
    {
        $name = '';
        try {
            $id = (int)$request->get('id');
            if (!$id) {
                $this->addFlash(
                    'danger',
                    "The id is not set."
                );
                return $this->redirectToRoute('admin_incometype_index');
            }

            $incomeType =  $this->getDoctrine()->getRepository('AppBundle:IncomeType')->find($id);
            if (!$incomeType) {
                $this->addFlash(
                    'danger',
                    "The income with this id is not exist."
                );
                return $this->redirectToRoute('admin_incometype_index');
            }
            $name = $incomeType->getName();
            $em = $this->getDoctrine()->getManager();

            $em->remove($incomeType);
            $em->flush();
            } catch (\Exception $e) {
                $this->addFlash(
                    'danger',
                    $e->getMessage()
                );
            }
        $this->addFlash(
            'success',
            "The income {$name} has deleted."
        );

            return $this->redirectToRoute('admin_incometype_index');
        }
}
