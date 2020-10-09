<?php

namespace App\Controller;

use App\Entity\Commune;
use App\Entity\User;
use App\Repository\CommuneRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class RegisterController extends AbstractController
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    /**
     * @Route("/api/register", name="register")
     */
    public function index()
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'RegisterController',
        ]);
    }
    /**
     * @Route("/api/register/login_check", name="register_login")
     * @return JsonResponse
     */
    public function api_login(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse([
            'mail' => $user->getMail(),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     * @Route("/api/register/user", name="userCreate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function userCreate(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $user
            ->setMail($data['mail'])
            ->setPassword($this->passwordEncoder->encodePassword($user, $data['password']))
            ->setRoles(['ROLE_USER']);
        $em->persist($user);
        $em->flush();
        return JsonResponse::fromJsonString($this->serializeJson($user));
    }

    /**
     * @Route("/api/register/update", name="userUpdate", methods={"PUT"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function userUpdate(Request $request, UserRepository $userRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(),true);
        $user = $userRepository->findOneBy(['id' => $data['id']]);

        if($data["mail"]){
            $user->setMail($data['mail']);
        }
        if($data["password"]){
            $user->setPassword($this->passwordEncoder->encodePassword($user, $data['password']));
        }
        $em->persist($user);
        $em->flush();
        return JsonResponse::fromJsonString($this->serializeJson($user));
    }

    /**
     * @Route("/api/register/delete", name="userDelete", methods={"DELETE"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function userDelete(Request $request, UserRepository $userRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(),true);
        $user = $userRepository->findOneBy(['id' => $data['id']]);

        $em->remove($user);
        $em->flush();
        return JsonResponse::fromJsonString($this->serializeJson($user));
    }

    private function serializeJson($objet)
    {
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getSlug();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return $serializer->serialize($objet, 'json');
    }
}
