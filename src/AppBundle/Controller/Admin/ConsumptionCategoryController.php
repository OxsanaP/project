<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\ConsumptionCategory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Repository\ConsumptionCategoryRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


/**
 * Category controller.
 *
 * @Route("admin/category")
 */
class ConsumptionCategoryController extends Controller
{
    /**
     * Lists all consumptionCategory entities.
     *
     * @Route("/", name="admin_category_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $dql   = "SELECT a FROM AppBundle:ConsumptionCategory a";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );
        $consumptionCategories = $em->getRepository('AppBundle:ConsumptionCategory')->findAll();

        return $this->render('admin/category/index.html.twig', array(
            'consumptionCategories' => $consumptionCategories,
            'pagination' => $pagination
        ));
    }

    /**
     * Creates a new consumptionCategory entity.
     *
     * @Route("/new", name="admin_category_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $consumptionCategory = new Consumptioncategory();
        $form = $this->createForm('AppBundle\Form\ConsumptionCategoryType', $consumptionCategory);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            try {
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $this->_setParentCategory($consumptionCategory, $form);
                    $em->persist($consumptionCategory);
                    $em->flush();

                    $this->addFlash(
                        'success',
                        'The category was added!'
                    );
                    return $this->redirectToRoute('admin_category_index');
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
        return $this->render('admin/category/new.html.twig', array(
            'consumptionCategory' => $consumptionCategory,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing consumptionCategory entity.
     *
     * @Route("/{id}/edit", name="admin_category_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ConsumptionCategory $consumptionCategory)
    {
        $editForm = $this->createForm('AppBundle\Form\ConsumptionCategoryType', $consumptionCategory);
        $editForm->add('isUser', ChoiceType::class, array(
            'choices' => array('No' => 1, 'Yes' => 0), 'label'=>'Visible for all users',
        ));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try{
                $this->_setParentCategory($consumptionCategory, $editForm);
                $this->getDoctrine()->getManager()->flush();
            } catch (\Exception $e) {
                $this->addFlash(
                    'danger',
                    $e->getMessage()
                );
            }

            $this->addFlash(
                'success',
                "The category {$consumptionCategory->getName()} has changed."
            );

            return $this->redirectToRoute('admin_category_index');
        }

        return $this->render('admin/category/edit.html.twig', array(
            'consumptionCategory' => $consumptionCategory,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Deletes a consumptionCategory entity.
     *
     * @Route("/{id}/delete", name="admin_category_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request)
    {
        $name = '';
        try {
            $id = (int)$request->get('id');
            if(!$id){
                $this->addFlash(
                    'danger',
                    "The id is not set."
                );
                return $this->redirectToRoute('admin_category_index');
            }

            $consumptionCategory =  $this->getDoctrine()->getRepository('AppBundle:ConsumptionCategory')->find($id);
            if (!$consumptionCategory) {
                $this->addFlash(
                    'danger',
                    "The category with this id is not exist."
                );
                return $this->redirectToRoute('admin_category_index');
            }
            $name = $consumptionCategory->getName();
            $em = $this->getDoctrine()->getManager();
            $em->remove($consumptionCategory);
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                $e->getMessage()
            );
        }
        $this->addFlash(
            'success',
            "The category {$name} has deleted."
        );
        return $this->redirectToRoute('admin_category_index');
    }

    /**
     * Set parent category from form extra data
     * @param ConsumptionCategory $consumptionCategory
     * @param $form
     * @return $this
     */
    public function _setParentCategory(ConsumptionCategory $consumptionCategory, $form)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $form->getExtraData();
        $id = (isset($data['parent_category'])) ? (int)$data['parent_category'] : null;
        $parentCategory = $em->getRepository('AppBundle:ConsumptionCategory')->find($id);
        $consumptionCategory->setParentCategory($parentCategory);
        return $this;
    }

    /**
     * Creates a form to delete a consumptionCategory entity.
     *
     * @param ConsumptionCategory $consumptionCategory The consumptionCategory entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ConsumptionCategory $consumptionCategory)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_category_delete', array('id' => $consumptionCategory->getId())))
            ->setMethod('GET')
            ->getForm();
    }
}
