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
    public function index(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('user_management/index.html.twig', [
            'users' => $users,
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



// namespace App\Controller;

// use App\Entity\User;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Routing\Attribute\Route;

// class UserManagementController extends AbstractController
// {
//     #[Route('/user-management', name: 'user_management')]
//     public function index(EntityManagerInterface $entityManager): Response
//     {
//         // Fetch all users from the database
//         $users = $entityManager->getRepository(User::class)->findAll();

//         // Render the user management view with the list of users
//         return $this->render('user_management/index.html.twig', [
//             'users' => $users,
//         ]);
//     }

//     #[Route('/user-management/block', name: 'block_users', methods: ['POST'])]
//     public function blockUsers(Request $request, EntityManagerInterface $entityManager): Response
//     {
//         $userIds = $request->request->get('userIds');

//         // Check if userIds is an array and not empty
//         if (!is_array($userIds) || empty($userIds)) {
//             $this->addFlash('error', 'No users selected.');
//             return $this->redirectToRoute('user_management');
//         }

//         // Block selected users
//         foreach ($userIds as $id) {
//             $user = $entityManager->getRepository(User::class)->find($id);
//             if ($user) {
//                 $user->setStatus('blocked');
//                 $entityManager->persist($user);
//             }
//         }
//         $entityManager->flush();

//         $this->addFlash('success', 'Selected users have been blocked.');
//         return $this->redirectToRoute('user_management');
//     }

//     #[Route('/user-management/delete', name: 'delete_users', methods: ['POST'])]
//     public function deleteUsers(Request $request, EntityManagerInterface $entityManager): Response
//     {
//         $userIds = $request->request->get('userIds');

//         // Check if userIds is an array and not empty
//         if (!is_array($userIds) || empty($userIds)) {
//             $this->addFlash('error', 'No users selected.');
//             return $this->redirectToRoute('user_management');
//         }

//         // Delete selected users
//         foreach ($userIds as $id) {
//             $user = $entityManager->getRepository(User::class)->find($id);
//             if ($user) {
//                 $entityManager->remove($user);
//             }
//         }
//         $entityManager->flush();

//         $this->addFlash('success', 'Selected users have been deleted.');
//         return $this->redirectToRoute('user_management');
//     }
// }
