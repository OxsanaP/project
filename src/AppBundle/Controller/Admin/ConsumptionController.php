<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Consumption;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;
use AppBundle\Repository\IncomeTypeRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Consumption controller.
 *
 * @Route("admin/consumption")
 */
class ConsumptionController extends Controller
{
    /**
     * Lists all consumption entities.
     *
     * @Route("/", name="admin_consumption_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $dql   = "SELECT a FROM AppBundle:Consumption a";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );

        $consumptions = $em->getRepository('AppBundle:Consumption')->findAll();

        return $this->render('admin/consumption/index.html.twig', array(
            'consumptions' => $consumptions,
            'pagination' => $pagination
        ));
    }

    /**
     * Creates a new consumption entity.
     *
     * @Route("/new", name="admin_consumption_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $consumption = new Consumption();
        $form = $this->createForm('AppBundle\Form\ConsumptionType', $consumption);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($consumption);
                    $em->flush();

                    $this->addFlash(
                        'success',
                        'The income was added!'
                    );
                    return $this->redirectToRoute('admin_consumption_index');
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
        $form->remove('category');
        return $this->render('admin/consumption/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing consumption entity.
     *
     * @Route("/{id}/edit", name="admin_consumption_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Consumption $consumption)
    {
        $editForm = $this->createForm('AppBundle\Form\ConsumptionType', $consumption);
        $editForm->add('isUser', ChoiceType::class, array(
            'choices' => array('No' => 1, 'Yes' => 0), 'label'=>'Visible for all users',
        ));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
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
                "The consumption {$consumption->getName()} has changed."
            );

            return $this->redirectToRoute('admin_consumption_index');

        }
        $editForm->remove('category');
        return $this->render('admin/consumption/edit.html.twig', array(
            'consumption' => $consumption,
            'edit_form' => $editForm->createView(),

        ));
    }

    /**
     * Deletes a consumption entity.
     *
     * @Route("/{id}/delete", name="admin_consumption_delete")
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
                return $this->redirectToRoute('admin_consumption_index');
            }
            $consumption =  $this->getDoctrine()->getRepository('AppBundle:Consumption')->find($id);
            if (!$consumption) {
                $this->addFlash(
                    'danger',
                    "The consumption with this id is not exist."
                );
                return $this->redirectToRoute('admin_consumption_index');
            }
            $name = $consumption->getName();
            $em = $this->getDoctrine()->getManager();

            $em->remove($consumption);
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                $e->getMessage()
            );
        }
        $this->addFlash(
            'success',
            "The consumption {$name} has deleted."
        );

        return $this->redirectToRoute('admin_consumption_index');
    }

    /**
     * Creates a form to delete a consumption entity.
     *
     * @param Consumption $consumption The consumption entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Consumption $consumption)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_consumption_delete', array('id' => $consumption->getId())))
            ->setMethod('GET')
            ->getForm()
        ;
    }
}
