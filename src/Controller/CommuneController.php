<?php

namespace App\Controller;

use App\Entity\Commune;
use App\Entity\Departements;
use App\Repository\CommuneRepository;
use App\Repository\DepartementsRepository;
use App\Repository\MediaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CommuneController extends AbstractController
{
    /**
     * @Route("/commune", name="commune")
     */
    public function index(CommuneRepository $communeRepository)
    {
        return $this->render('commune/index.html.twig', [
            'controller_name' => 'CommuneController',
            'commune' => $communeRepository->findAll()
        ]);
    }

    /**
     * @Route("/api/commune/json", name="indexJson")
     * @param CommuneRepository $communeRepository
     */
    public function indexJson(CommuneRepository $communeRepository)
    {
        $commune = $communeRepository->findAll();
        $jsonContent = $this->serializeJson($commune);
        $response = JsonResponse::fromJsonString($jsonContent);
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * @Route("/commune/{slug}", name="getcommunebyslug")
     */
    public function getcommunebyslug(string $slug,CommuneRepository $communeRepository)
    {
        return $this->render('departements/index.html.twig', [
            'controller_name' => 'PresentationController',
            'commune' => $communeRepository->findBy(['slug' => $slug])
        ]);
    }

    /**
     * @Route("/commune/json/{slug}", name="jsongetdepartementbyslug")
     * @param Commune $commune
     * @return JsonResponse
     */
    public function jsongetcommunebyslug(Commune $commune)
    {
        return JsonResponse::fromJsonString($this->serializeJson($commune));
    }

    /**
     * @Route("/api/commune", name="commune", methods={"GET"})
     * @param CommuneRepository $communeRepository
     * @param Request $request
     * @return JsonResponse
     */
    public function commune(CommuneRepository $communeRepository, Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(Commune::class)->getFieldNames();
        foreach($metadata as $value){
            if ($request->query->get($value)){
                $filter[$value] = $request->query->get($value);
            }
        }
        return JsonResponse::fromJsonString($this->serializeJson($communeRepository->findBy($filter)));

    }

    /**
     * @Route("/api/commune/create", name="communeCreate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function communeCreate(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);
        $commune = new Commune();
        $commune
            ->setNom($data['nom'])
            ->setLon($data['lon'])
            ->setLat($data['lat']);
        if ($data['CodePostal'])
            $commune->setCodePostal($data['CodePostal']);
        if ($data['Code'])
            $commune->setCode($data['Code']);
        if ($data['CodeDepartement'])
            $commune->setCodeDepartement($data['CodeDepartement']);
        if ($data['CodeRegion'])
            $commune->setCodeRegion($data['CodeRegion']);
        $em->persist($commune);
        $em->flush();
        return JsonResponse::fromJsonString($this->serializeJson($commune));
    }

    /**
     * @Route("/api/commune/update", name="communeUpdate", methods={"PUT"})
     * @param Request $request
     * @param CommuneRepository $communeRepository
     * @param MediaRepository $mediaRepository
     * @return JsonResponse
     */
    public function communeUpdate(Request $request, CommuneRepository $communeRepository, MediaRepository $mediaRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(),true);
        $commune = $communeRepository->findOneBy(['id' => $data['id']]);

        if($data["nom"]){
            $commune->setNom($data['nom']);
        }
        if($data["lon"]){
            $commune->setLon($data['lon']);
        }
        if($data["lat"]){
            $commune->setLat($data['lat']);
        }
        if($data["code"]){
            $commune->setCode($data['code']);
        }
        if($data["codeDepartement"]){
            $commune->setCodeDepartement($data['codeDepartement']);
        }
        if($data["codeRegion"]){
            $commune->setCodeRegion($data['codeRegion']);
        }
        if($data["codePostal"]){
            $commune->setCodePostal($data['codePostal']);
        }
        $em->persist($commune);
        $em->flush();
        return JsonResponse::fromJsonString($this->serializeJson($commune));
    }

    /**
     * @Route("/api/commune/delete", name="userDelete", methods={"DELETE"})
     * @param Request $request
     * @param CommuneRepository $communeRepository
     * @return JsonResponse
     */
    public function communeDelete(Request $request, CommuneRepository $communeRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(),true);
        $commune = $communeRepository->findOneBy(['id' => $data['id']]);

        $em->remove($commune);
        $em->flush();
        return JsonResponse::fromJsonString($this->serializeJson($commune));
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
