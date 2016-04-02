<?php

namespace Eshop\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eshop\ShopBundle\Entity\Slide;
use Eshop\ShopBundle\Form\Type\SlideType;

/**
 * Slide controller.
 *
 * @Route("/admin/slide")
 */
class SlideController extends Controller
{
    /**
     * Lists all Slide entities.
     *
     * @Route("/", name="admin_slide")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ShopBundle:Slide')->findBy(array(), array('slideOrder' => 'ASC'));

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Displays a form to create a new Slide entity.
     *
     * @Route("/new", name="admin_slide_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $slide = new Slide();
        $form = $this->createForm('Eshop\ShopBundle\Form\Type\SlideType', $slide);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($slide);
            $em->flush();

            return $this->redirectToRoute('admin_slide_show', array('id' => $slide->getId()));
        }

        return array(
            'entity' => $slide,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Slide entity.
     *
     * @Route("/{id}", name="admin_slide_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Slide $slide)
    {
        $deleteForm = $this->createDeleteForm($slide);

        return array(
            'entity' => $slide,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Slide entity.
     *
     * @Route("/{id}/edit", name="admin_slide_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Slide $slide)
    {
        $deleteForm = $this->createDeleteForm($slide);
        $editForm = $this->createForm('Eshop\ShopBundle\Form\Type\SlideType', $slide);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if ($editForm->get('file')->getData() !== null) { // if any file was updated
                $file = $editForm->get('file')->getData();
                $slide->removeUpload(); // remove old file, see this at the bottom
                $slide->setPath(($file->getClientOriginalName())); // set Image Path because preUpload and upload method will not be called if any doctrine entity will not be changed. It tooks me long time to learn it too.
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($slide);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirectToRoute('admin_slide_edit', array('id' => $slide->getId()));
        }

        return array(
            'entity' => $slide,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Slide entity.
     *
     * @Route("/{id}", name="admin_slide_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Slide $slide)
    {
        $form = $this->createDeleteForm($slide);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($slide);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_slide'));
    }

    /**
     * Creates a form to delete a Slide entity by id.
     *
     * @param Slide $slide The Slide entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Slide $slide)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_slide_delete', array('id' => $slide->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
