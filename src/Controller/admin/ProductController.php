<?php

namespace App\Controller\admin;

use App\Repository\ImageRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use App\Entity\Image;
use App\Form\Type\ProductType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;

/**
 * Product controller.
 *
 * @Route("/admin/product")
 */
class ProductController extends AbstractController
{
    /**
     * Lists all Product entities.
     *
     * @Route("/", methods={"GET"}, name="admin_product")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(
        Request $request,
        ProductRepository $productRepository,
        PaginatorInterface $paginator
    ): Response {
        //if search is required
        $searchWords = trim($request->get('search_words'));

        $qb = $productRepository->searchProductsAdminQB($searchWords);
        $limit = $this->getParameter('admin_products_pagination_count');

        $products = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            $limit
        );

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
            'search_words' => $searchWords
        ]);
    }

    /**
     * Displays a form to create a new Product entity.
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_product_new")
     * @param Request $request
     * @param ImageRepository $imageRepository
     * @return Response
     */
    public function new(Request $request, ImageRepository $imageRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //update uploaded images entities
            $imageIdString = $request->request->get('filenames');
            if ($imageIdString !== '') {
                $imageIdArray = explode(',', $imageIdString);
                array_pop($imageIdArray);

                $em = $this->getDoctrine()->getManager();
                foreach ($imageIdArray as $imageId) {
                    $image = $imageRepository->find($imageId);
                    $image->setProduct($product);
                    $product->addImage($image);
                    $em->persist($image);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('admin_product_show', [
                'id' => $product->getId()
            ]);
        }

        return $this->render('admin/product/new.html.twig', [
            'entity' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * Finds and displays a Product entity.
     *
     * @Route("/{id}", methods={"GET"}, name="admin_product_show")
     * @param Product $product
     * @return Response
     */
    public function show(Product $product): Response
    {
        $deleteForm = $this->createDeleteForm($product);

        if ($product->getDeleted()) {
            return $this->render('admin/product/deleted.html.twig');
        }

        return $this->render('admin/product/show.html.twig', [
            'entity' => $product,
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     * Displays a form to edit an existing Product entity.
     *
     * @Route("/{id}/edit", methods={"GET", "POST"}, name="admin_product_edit")
     * @param Request $request
     * @param Product $product
     * @param ImageRepository $imageRepository
     * @return Response
     */
    public function edit(Request $request, Product $product, ImageRepository $imageRepository): Response
    {
        $deleteForm = $this->createDeleteForm($product);
        $editForm = $this->createForm(ProductType::class, $product);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //update uploaded images entities
            $imageIdString = $request->request->get('filenames');
            if ($imageIdString !== '') {
                $imageIdArray = explode(',', $imageIdString);
                array_pop($imageIdArray);

                $em = $this->getDoctrine()->getManager();
                foreach ($imageIdArray as $imageId) {
                    $image = $imageRepository->find($imageId);
                    $image->setProduct($product);
                    $product->addImage($image);
                    $em->persist($image);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirectToRoute('admin_product_edit', [
                'id' => $product->getId()
            ]);
        }

        return $this->render('admin/product/edit.html.twig', [
            'entity' => $product,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     * Deletes a Product entity.
     *
     * @Route("/{id}", methods={"DELETE"}, name="admin_product_delete")
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function delete(Request $request, Product $product): Response
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_product'));
    }

    /**
     * @Route("/remove_image", methods={"POST"} , name="remove_image", defaults={"_format"="json"})
     * @param Request $request
     * @param ImageRepository $imageRepository
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeImage(Request $request, ImageRepository $imageRepository): Response
    {
        $requestData = $request->request;
        $imageEntityId = (int)$requestData->get('imageEntityId');
        /**
         * @var EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();
        /**
         * @var Image $image , $imageRepository
         */
        $image = $imageRepository->find($imageEntityId);
        $product = $image->getProduct();
        $product->removeImage($image);

        $em->persist($product);
        $em->remove($image);

        $em->flush();

        $data = json_encode(['success' => true]);
        $headers = ['Content-type' => 'application-json; charset=utf8'];
        return new Response($data, 200, $headers);
    }

    /**
     * Creates a form to delete a Product entity by id.
     *
     * @param Product $product The Product id
     * @return FormInterface
     */
    private function createDeleteForm(Product $product): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_product_delete', ['id' => $product->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
