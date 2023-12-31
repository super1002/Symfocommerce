<?php

namespace App\Controller\admin;

use App\Form\Type\ManufacturerType;
use App\Repository\ManufacturerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Manufacturer;

/**
 * Manufacturer controller.
 *
 * @Route("/admin/manufacturer")
 */
class ManufacturerController extends AbstractController
{
    /**
     * Lists all Manufacturer entities.
     *
     * @Route("/", methods={"GET"}, name="admin_manufacturer")
     * @param Request $request
     * @param ManufacturerRepository $manufacturerRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(
        Request $request,
        ManufacturerRepository $manufacturerRepository,
        PaginatorInterface $paginator
    ): Response {
        $qb = $manufacturerRepository->getAllManufacturersAdminQB();
        $limit = $this->getParameter('admin_manufacturers_pagination_count');

        $manufacturers = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            $limit
        );

        return $this->render('admin/manufacturer/index.html.twig', [
            'entities' => $manufacturers
        ]);
    }

    /**
     * Displays a form to create a new Manufacturer entity.
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_manufacturer_new")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $manufacturer = new Manufacturer();
        $form = $this->createForm(ManufacturerType::class, $manufacturer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($manufacturer);
            $em->flush();

            return $this->redirectToRoute('admin_manufacturer_show', [
                'id' => $manufacturer->getId()
            ]);
        }

        return $this->render('admin/manufacturer/new.html.twig', [
            'entity' => $manufacturer,
            'form' => $form->createView()
        ]);
    }

    /**
     * Finds and displays a Manufacturer entity.
     *
     * @Route("/{id}", methods={"GET"}, name="admin_manufacturer_show")
     * @param Manufacturer $manufacturer
     * @return Response
     */
    public function show(Manufacturer $manufacturer): Response
    {
        $deleteForm = $this->createDeleteForm($manufacturer);

        return $this->render('admin/manufacturer/show.html.twig', [
            'entity' => $manufacturer,
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     * Displays a form to edit an existing Manufacturer entity.
     *
     * @Route("/{id}/edit", methods={"GET", "POST"}, name="admin_manufacturer_edit")
     * @param Request $request
     * @param Manufacturer $manufacturer
     * @return Response
     */
    public function edit(Request $request, Manufacturer $manufacturer): Response
    {
        $deleteForm = $this->createDeleteForm($manufacturer);
        $editForm = $this->createForm(ManufacturerType::class, $manufacturer);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($manufacturer);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirectToRoute('admin_manufacturer_edit', [
                'id' => $manufacturer->getId()
            ]);
        }

        return $this->render('admin/manufacturer/edit.html.twig', [
            'entity' => $manufacturer,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     * Deletes a Manufacturer entity.
     *
     * @Route("/{id}", methods={"DELETE"}, name="admin_manufacturer_delete")
     * @param Request $request
     * @param Manufacturer $manufacturer
     * @return Response
     */
    public function delete(Request $request, Manufacturer $manufacturer): Response
    {
        $form = $this->createDeleteForm($manufacturer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($manufacturer);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_manufacturer'));
    }

    /**
     * Creates a form to delete a Manufacturer entity by id.
     *
     * @param Manufacturer $manufacturer The Manufacturer entity
     * @return FormInterface
     */
    private function createDeleteForm(Manufacturer $manufacturer): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_manufacturer_delete', ['id' => $manufacturer->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
