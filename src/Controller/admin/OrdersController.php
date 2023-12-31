<?php

namespace App\Controller\admin;

use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Repository\OrdersRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Orders;

/**
 * Orders controller.
 *
 * @Route("/admin/orders")
 */
class OrdersController extends AbstractController
{
    /**
     * Lists all Orders entities.
     *
     * @Route("/", methods={"GET"}, name="admin_orders")
     * @param Request $request
     * @param OrdersRepository $ordersRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(
        Request $request,
        OrdersRepository $ordersRepository,
        PaginatorInterface $paginator
    ): Response {
        $qb = $ordersRepository->getAllOrdersAdminQB();
        $limit = $this->getParameter('admin_manufacturers_pagination_count');

        $orders = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            $limit
        );

        return $this->render('admin/orders/index.html.twig', [
            'orders' => $orders
        ]);
    }

    /**
     * Finds and displays a Orders entity.
     *
     * @Route("/{id}", methods={"GET"}, name="admin_order_show")
     * @param int $id
     * @param OrdersRepository $ordersRepository
     * @return Response
     */
    public function show(int $id, OrdersRepository $ordersRepository): Response
    {
        $order = $ordersRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Unable to find Order entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $orderProducts = $order->getOrderProducts();
        $productsArray = [];

        foreach ($orderProducts as $orderProduct) {
            $productPosition = [];
            /**
             * @var Product $product
             * @var OrderProduct $orderProduct
             */
            $product = $orderProduct->getProduct();
            $price = $orderProduct->getPrice();
            $quantity = $orderProduct->getQuantity();
            $sum = $price * $quantity;

            $productPosition['product'] = $product;
            $productPosition['quantity'] = $quantity;
            $productPosition['price'] = $price;
            $productPosition['sum'] = $sum;

            $productsArray[] = $productPosition;
        }

        return $this->render('admin/orders/show.html.twig', [
            'order' => $order,
            'delete_form' => $deleteForm->createView(),
            'products' => $productsArray
        ]);
    }


    /**
     * Deletes a Orders entity.
     *
     * @Route("/{id}", methods={"DELETE"}, name="admin_order_delete")
     * @param Request $request
     * @param int $id
     * @param OrdersRepository $ordersRepository
     * @return Response
     */
    public function delete(Request $request, int $id, OrdersRepository $ordersRepository): Response
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $ordersRepository->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Order entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_orders'));
    }

    /**
     * Creates a form to delete a Orders entity by id.
     *
     * @param int $id The entity id
     * @return FormInterface
     */
    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_order_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
