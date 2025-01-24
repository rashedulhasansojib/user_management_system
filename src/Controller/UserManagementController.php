<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Entity\User;

class UserManagementController extends AbstractController
{
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    #[Route('/user-management', name: 'user_management')]
    public function index(Request $request): Response
    {
        // Check if the user is logged in
        if (!$this->getUser()) {
            // Redirect to the login page if not authenticated
            return $this->redirectToRoute('app_login');
        }
        // Get sorting parameters from the request
        $sortField = $request->query->get('sort', 'id'); // Default sort by 'id'
        $sortOrder = $request->query->get('order', 'ASC'); // Default order is 'ASC'

        // Validate sort field and order
        $validSortFields = ['id', 'name', 'email', 'status', 'lastLogin'];
        $validSortOrders = ['ASC', 'DESC'];

        if (!in_array($sortField, $validSortFields)) {
            $sortField = 'id'; // Fallback to default
        }

        if (!in_array($sortOrder, $validSortOrders)) {
            $sortOrder = 'ASC'; // Fallback to default
        }

        // Fetch sorted users
        $users = $this->userRepository->findAllSorted($sortField, $sortOrder);

        return $this->render('user_management/index.html.twig', [
            'users' => $users,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/user-management/block', name: 'user_block', methods: ['POST'])]
    public function block(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $userIds = $request->request->all('userIds');

        if (empty($userIds)) {
            $this->addFlash('warning', 'No users selected for blocking.');
            return $this->redirectToRoute('user_management');
        }

        foreach ($userIds as $id) {
            $user = $userRepository->find($id);
            if ($user) {
                $user->setStatus('blocked');
                $entityManager->persist($user);
            }
        }

        $entityManager->flush();
        $this->addFlash('success', 'Selected users have been blocked.');

        return $this->redirectToRoute('user_management');
    }

    #[Route('/user-management/unblock', name: 'user_unblock', methods: ['POST'])]
    public function unblock(Request $request): Response
    {
        $userIds = $request->request->all('userIds');

        if (empty($userIds)) {
            $this->addFlash('warning', 'No users selected for unblocking.');
            return $this->redirectToRoute('user_management');
        }

        foreach ($userIds as $id) {
            $user = $this->userRepository->find($id);
            if ($user) {
                $user->setStatus('active'); // Assuming 'active' is the status for unblocked users
                $this->entityManager->persist($user);
            }
        }

        $this->entityManager->flush();
        $this->addFlash('success', 'Selected users have been unblocked.');

        return $this->redirectToRoute('user_management');
    }

    #[Route('/user-management/delete', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $userIds = $request->request->all('userIds');

        if (empty($userIds)) {
            $this->addFlash('warning', 'No users selected for deletion.');
            return $this->redirectToRoute('user_management');
        }

        foreach ($userIds as $id) {
            $user = $userRepository->find($id);
            if ($user) {
                $entityManager->remove($user);
            }
        }

        $entityManager->flush();
        $this->addFlash('success', 'Selected users have been deleted.');

        return $this->redirectToRoute('user_management');
    }
}
