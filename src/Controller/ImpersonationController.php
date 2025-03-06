<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
class ImpersonationController extends AbstractController
{
    #[Route('/impersonate', name: 'impersonate_user', methods: ['GET', 'POST'])]
    public function impersonate(
        Request $request,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ): Response {
        // Fetch distinct roles from users
        $rolesData = $em->getRepository(User::class)->createQueryBuilder('u')
            ->select('u.roles')
            ->getQuery()
            ->getResult();
    
        $roles = [];
        foreach ($rolesData as $roleSet) {
            if (is_string($roleSet['roles'])) {
                $decodedRoles = json_decode($roleSet['roles'], true);
                if (is_array($decodedRoles)) {
                    $roles = array_merge($roles, $decodedRoles);
                }
            } elseif (is_array($roleSet['roles'])) {
                $roles = array_merge($roles, $roleSet['roles']);
            }
        }
        $roles = array_unique($roles);
    
        // Get selected role from the request
        $selectedRole = $request->query->get('role');
        $users = [];
    
        if ($selectedRole) {
            $users = $em->getRepository(User::class)->createQueryBuilder('u')
                ->where(':role MEMBER OF u.roles')
                ->setParameter('role', $selectedRole)
                ->getQuery()
                ->getResult();
        }
    
        // Handle user impersonation
        if ($request->isMethod('POST')) {
            $userId = $request->request->get('user');
            $user = $em->getRepository(User::class)->find($userId);
    
            if ($user) {
                $firewallName = 'main'; // Remplace 'main' par le bon nom du firewall si différent
                $token = new UsernamePasswordToken($user, $firewallName, $user->getRoles());
                $tokenStorage->setToken($token);
    
                // Redirect after impersonation to messages page
                return $this->redirectToRoute('messenger');
            }
        }
    
        return $this->render('form/impersonation.html.twig', [
            'roles' => $roles,
            'users' => $users,
            'selectedRole' => $selectedRole
        ]);
    }
    
    #[Route('/impersonate/users', name: 'get_users_by_role', methods: ['GET'])]
public function getUsersByRole(Request $request, EntityManagerInterface $em): Response
{
    $selectedRole = $request->query->get('role');
    
    if (!$selectedRole) {
        return $this->json([]); // Return empty array if no role is selected
    }

    // Debugging: Log received role and database query
    dump($selectedRole);

    $users = $em->getRepository(User::class)->createQueryBuilder('u')
    ->where('u.roles LIKE :role')
    ->setParameter('role', '%'.$selectedRole.'%')
    ->getQuery()
    ->getResult();


    dump($users); // Check if users are retrieved correctly

    $userData = array_map(fn($user) => [
        'id' => $user->getId(),
        'name' => $user->getName()
    ], $users);

    return $this->json($userData);
}

}
