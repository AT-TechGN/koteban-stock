<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Entity\Supplier;
use App\Entity\User;
use App\Entity\Variant;
use App\Form\CategoryType;
use App\Form\OrderItemType;
use App\Form\OrderType;
use App\Form\ProductType;
use App\Form\PurchaseItemType;
use App\Form\PurchaseType;
use App\Form\SupplierType;
use App\Form\UserType;
use App\Form\VariantType;
use App\Repository\CategoryRepository;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\PurchaseItemRepository;
use App\Repository\PurchaseRepository;
use App\Repository\SupplierRepository;
use App\Repository\UserRepository;
use App\Repository\VariantRepository;
use App\Service\InvoiceGenerator;
use App\Service\StatisticsCalculator;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    // Dashboard
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        StatisticsCalculator $statisticsCalculator,
        OrderRepository $orderRepository,
        UserRepository $userRepository
    ): Response {
        // Données pour les cartes
        $totalOrders = $orderRepository->count([]);
        $totalSales = $statisticsCalculator->getTotalSales();
        $activeUsers = $userRepository->count(['isActive' => true]);
        $totalRevenue = $statisticsCalculator->getTotalRevenue();

        // Transactions récentes (commandes)
        $recentOrders = $orderRepository->findBy([], ['createdAt' => 'DESC'], 8);

        return $this->render('admin/index.html.twig', [
            'total_orders' => $totalOrders,
            'total_sales' => $totalSales,
            'active_users' => $activeUsers,
            'total_revenue' => $totalRevenue,
            'recent_orders' => $recentOrders,
        ]);
    }

    // User Management
    #[Route('/user/register', name: 'app_admin_user_register', methods: ['GET', 'POST'])]
    public function userRegister(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie !');
            return $this->redirectToRoute('app_admin_user_manage');
        }

        return $this->render('admin/user/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/user/manage', name: 'app_admin_user_manage', methods: ['GET'])]
    public function userManage(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/user/manage.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/user/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function userEdit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true,
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('plainPassword') && $form->get('plainPassword')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                );
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès');
            return $this->redirectToRoute('app_admin_user_manage');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    // Category Management
    #[Route('/category', name: 'app_admin_category_index', methods: ['GET'])]
    public function categoryIndex(CategoryRepository $categoryRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $categoryRepository->createQueryBuilder('c')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/category/index.html.twig', [
            'categories' => $pagination,
        ]);
    }

    #[Route('/category/new', name: 'app_admin_category_new', methods: ['GET', 'POST'])]
    public function categoryNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie créée avec succès');
            return $this->redirectToRoute('app_admin_category_index');
        }

        return $this->render('admin/category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/category/{id}/edit', name: 'app_admin_category_edit', methods: ['GET', 'POST'])]
    public function categoryEdit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie mise à jour avec succès');
            return $this->redirectToRoute('app_admin_category_index');
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    // Product Management
    #[Route('/product', name: 'app_admin_product_index', methods: ['GET'])]
    public function productIndex(ProductRepository $productRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $productRepository->createQueryBuilder('p')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/product/index.html.twig', [
            'products' => $pagination,
        ]);
    }

    #[Route('/product/new', name: 'app_admin_product_new', methods: ['GET', 'POST'])]
    public function productNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit créé avec succès');
            return $this->redirectToRoute('app_admin_product_index');
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/{id}/edit', name: 'app_admin_product_edit', methods: ['GET', 'POST'])]
    public function productEdit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Produit mis à jour avec succès');
            return $this->redirectToRoute('app_admin_product_index');
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    // Variant Management
    #[Route('/variant', name: 'app_admin_variant_index', methods: ['GET'])]
    public function variantIndex(VariantRepository $variantRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $variantRepository->createQueryBuilder('v')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/variant/index.html.twig', [
            'variants' => $pagination,
        ]);
    }

    #[Route('/variant/new', name: 'app_admin_variant_new', methods: ['GET', 'POST'])]
    public function variantNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $variant = new Variant();
        $form = $this->createForm(VariantType::class, $variant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($variant);
            $entityManager->flush();

            $this->addFlash('success', 'Variante créée avec succès');
            return $this->redirectToRoute('app_admin_variant_index');
        }

        return $this->render('admin/variant/new.html.twig', [
            'variant' => $variant,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/variant/{id}/edit', name: 'app_admin_variant_edit', methods: ['GET', 'POST'])]
    public function variantEdit(Request $request, Variant $variant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VariantType::class, $variant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Variante mise à jour avec succès');
            return $this->redirectToRoute('app_admin_variant_index');
        }

        return $this->render('admin/variant/edit.html.twig', [
            'variant' => $variant,
            'form' => $form->createView(),
        ]);
    }

    // Supplier Management
    #[Route('/supplier', name: 'app_admin_supplier_index', methods: ['GET'])]
    public function supplierIndex(SupplierRepository $supplierRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $supplierRepository->createQueryBuilder('s')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/supplier/index.html.twig', [
            'suppliers' => $pagination,
        ]);
    }

    #[Route('/supplier/new', name: 'app_admin_supplier_new', methods: ['GET', 'POST'])]
    public function supplierNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $supplier = new Supplier();
        $form = $this->createForm(SupplierType::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($supplier);
            $entityManager->flush();

            $this->addFlash('success', 'Fournisseur créé avec succès');
            return $this->redirectToRoute('app_admin_supplier_index');
        }

        return $this->render('admin/supplier/new.html.twig', [
            'supplier' => $supplier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supplier/{id}/edit', name: 'app_admin_supplier_edit', methods: ['GET', 'POST'])]
    public function supplierEdit(Request $request, Supplier $supplier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SupplierType::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Fournisseur mis à jour avec succès');
            return $this->redirectToRoute('app_admin_supplier_index');
        }

        return $this->render('admin/supplier/edit.html.twig', [
            'supplier' => $supplier,
            'form' => $form->createView(),
        ]);
    }

    // Purchase Management
    #[Route('/purchase', name: 'app_admin_purchase_index', methods: ['GET'])]
    public function purchaseIndex(PurchaseRepository $purchaseRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $purchaseRepository->createQueryBuilder('p')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/purchase/index.html.twig', [
            'purchases' => $pagination,
        ]);
    }

    #[Route('/purchase/new', name: 'app_admin_purchase_new', methods: ['GET', 'POST'])]
    public function purchaseNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $purchase = new Purchase();
        $form = $this->createForm(PurchaseType::class, $purchase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($purchase);
            $entityManager->flush();

            $this->addFlash('success', 'Achat créé avec succès');
            return $this->redirectToRoute('app_admin_purchase_index');
        }

        return $this->render('admin/purchase/new.html.twig', [
            'purchase' => $purchase,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/purchase/{id}/edit', name: 'app_admin_purchase_edit', methods: ['GET', 'POST'])]
    public function purchaseEdit(Request $request, Purchase $purchase, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PurchaseType::class, $purchase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Achat mis à jour avec succès');
            return $this->redirectToRoute('app_admin_purchase_index');
        }

        return $this->render('admin/purchase/edit.html.twig', [
            'purchase' => $purchase,
            'form' => $form->createView(),
        ]);
    }

    // PurchaseItem Management
    #[Route('/purchase-item', name: 'app_admin_purchase_item_index', methods: ['GET'])]
    public function purchaseItemIndex(PurchaseItemRepository $purchaseItemRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $purchaseItemRepository->createQueryBuilder('pi')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/purchase_item/index.html.twig', [
            'purchase_items' => $pagination,
        ]);
    }

    #[Route('/purchase-item/new', name: 'app_admin_purchase_item_new', methods: ['GET', 'POST'])]
    public function purchaseItemNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $purchaseItem = new PurchaseItem();
        $form = $this->createForm(PurchaseItemType::class, $purchaseItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($purchaseItem);
            $entityManager->flush();

            $this->addFlash('success', 'Article d\'achat créé avec succès');
            return $this->redirectToRoute('app_admin_purchase_item_index');
        }

        return $this->render('admin/purchase_item/new.html.twig', [
            'purchase_item' => $purchaseItem,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/purchase-item/{id}/edit', name: 'app_admin_purchase_item_edit', methods: ['GET', 'POST'])]
    public function purchaseItemEdit(Request $request, PurchaseItem $purchaseItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PurchaseItemType::class, $purchaseItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Article d\'achat mis à jour avec succès');
            return $this->redirectToRoute('app_admin_purchase_item_index');
        }

        return $this->render('admin/purchase_item/edit.html.twig', [
            'purchase_item' => $purchaseItem,
            'form' => $form->createView(),
        ]);
    }

    // Order Management
    #[Route('/order', name: 'app_admin_order_index', methods: ['GET'])]
    public function orderIndex(OrderRepository $orderRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $orderRepository->createQueryBuilder('o')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/order/index.html.twig', [
            'orders' => $pagination,
        ]);
    }

    #[Route('/order/new', name: 'app_admin_order_new', methods: ['GET', 'POST'])]
    public function orderNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            $this->addFlash('success', 'Commande créée avec succès');
            return $this->redirectToRoute('app_admin_order_index');
        }

        return $this->render('admin/order/new.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/order/{id}/edit', name: 'app_admin_order_edit', methods: ['GET', 'POST'])]
    public function orderEdit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Commande mise à jour avec succès');
            return $this->redirectToRoute('app_admin_order_index');
        }

        return $this->render('admin/order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    // OrderItem Management
    #[Route('/order-item', name: 'app_admin_order_item_index', methods: ['GET'])]
    public function orderItemIndex(OrderItemRepository $orderItemRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $orderItemRepository->createQueryBuilder('oi')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/order_item/index.html.twig', [
            'order_items' => $pagination,
        ]);
    }

    #[Route('/order-item/new', name: 'app_admin_order_item_new', methods: ['GET', 'POST'])]
    public function orderItemNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $orderItem = new OrderItem();
        $form = $this->createForm(OrderItemType::class, $orderItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($orderItem);
            $entityManager->flush();

            $this->addFlash('success', 'Article de commande créé avec succès');
            return $this->redirectToRoute('app_admin_order_item_index');
        }

        return $this->render('admin/order_item/new.html.twig', [
            'order_item' => $orderItem,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/order-item/{id}/edit', name: 'app_admin_order_item_edit', methods: ['GET', 'POST'])]
    public function orderItemEdit(Request $request, OrderItem $orderItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderItemType::class, $orderItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Article de commande mis à jour avec succès');
            return $this->redirectToRoute('app_admin_order_item_index');
        }

        return $this->render('admin/order_item/edit.html.twig', [
            'order_item' => $orderItem,
            'form' => $form->createView(),
        ]);
    }

    // Stock Management
    #[Route('/stock', name: 'app_admin_stock_index', methods: ['GET'])]
    public function stockIndex(StockManager $stockManager): Response
    {
        return $this->render('admin/stock/index.html.twig', [
            'low_stock_variants' => $stockManager->getLowStockVariants(),
        ]);
    }

    #[Route('/stock/history', name: 'app_admin_stock_history', methods: ['GET'])]
    public function stockHistory(): Response
    {
        return $this->render('admin/stock/history.html.twig', [
            'history' => [], // Placeholder, nécessite StockMovement
        ]);
    }

    #[Route('/stock/inventory', name: 'app_admin_stock_inventory', methods: ['GET'])]
    public function stockInventory(): Response
    {
        return $this->render('admin/stock/inventory.html.twig', [
            'inventory' => [], // Placeholder
        ]);
    }

    // Billing Management
    #[Route('/billing/history', name: 'app_admin_billing_history', methods: ['GET'])]
    public function billingHistory(OrderRepository $orderRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $orderRepository->createQueryBuilder('o')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/billing/history.html.twig', [
            'orders' => $pagination,
        ]);
    }

    #[Route('/billing/invoice/{id}', name: 'app_admin_billing_invoice', methods: ['GET'])]
    public function billingInvoice(Order $order, InvoiceGenerator $invoiceGenerator): Response
    {
        $filePath = $invoiceGenerator->generateInvoice($order);
        return $this->file($filePath, sprintf('invoice_%d.pdf', $order->getId()));
    }

    // Statistics Management
    #[Route('/statistics', name: 'app_admin_statistics_index', methods: ['GET'])]
    public function statisticsIndex(StatisticsCalculator $statisticsCalculator): Response
    {
        return $this->render('admin/statistics/index.html.twig', [
            'top_products' => $statisticsCalculator->getTopProducts(),
            'stock_value' => $statisticsCalculator->getStockValue(),
        ]);
    }

    // Search Management
    #[Route('/search', name: 'app_admin_search_index', methods: ['GET'])]
    public function searchIndex(Request $request, ProductRepository $productRepository, PaginatorInterface $paginator): Response
    {
        $query = $request->query->get('q', '');
        $products = $productRepository->createQueryBuilder('p')
            ->where('p.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery();

        $pagination = $paginator->paginate(
            $products,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/search/index.html.twig', [
            'products' => $pagination,
            'query' => $query,
        ]);
    }

    // Profile
    #[Route('/user/profile', name: 'app_profile', methods: ['GET'])]
    public function userProfile(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('admin/user/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    // Notification 
    #[Route('/notifications', name: 'app_notification', methods: ['GET'])]
    public function notifications(): Response
    {
        // Pour l'instant, on peut juste rendre une page simple ou afficher une liste vide
        return $this->render('notifications/index.html.twig', [
            // passe les données nécessaires ici
        ]);
    }

    // Envoyer un email
    #[Route('/mail', name: 'app_mail', methods: ['GET'])]
    public function mail(): Response
    {
        return $this->render('mail/index.html.twig', [
            // données à passer à la vue
        ]);
    }

    // Support
    #[Route('/support', name: 'app_support', methods: ['GET'])]
    public function support(): Response
    {
        return $this->render('support/index.html.twig', [
            // données éventuelles
        ]);
    }

    // Login
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupérer le dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}