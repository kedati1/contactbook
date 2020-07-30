<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/user/new", name="contactbook_new")
     */
    public function new()
    {
        return $this->render('user/new.html.twig', []);
    }

    /**
     * @Route("/user/{id}", name="contactbook")
     */
    public function show()
    {
        $user = $this->getUser();
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'contacts' => $user->getContacts()
        ]);
    }

    /**
     * @Route("/user/", name="contactbook_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        $password = $request->request->get('password');
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $request->request->get('email')]);
        if ($user) {
            $this->addFlash('notice', 'that email already exists');
            return $this->redirectToRoute('contactbook_new');
        } else {
            $user = new User();
            $user->setEmail($request->request->get('email'));
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/user/{id}/contact", name="contact_create", methods={"POST"})
     */
    public function create_contact(Request $request)
    {
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $contact = new Contact();
        $contact->setName($request->request->get('name'));
        $contact->setTel($request->request->get('tel'));
        $contact->setUserId($user->getEmail());
        $entityManager->persist($contact);
        $entityManager->flush();
        return $this->redirectToRoute('contactbook', ['id' => $user->getId()]);
    }
}