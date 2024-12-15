<?php

namespace App\Controller;

use App\Entity\Cat;
use App\Repository\CatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class CatController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private CatRepository $catRepository;

    public function __construct(EntityManagerInterface $entityManager, CatRepository $catRepository)
    {
        $this->entityManager = $entityManager;
        $this->catRepository = $catRepository;
    }

    public function validateBody(Request $request, array $expectedFields, array $requiredFields)
    {
        $data = $request->toArray();
        if (empty($data)) {
            return ['status' => false, 'message' => 'No data provided'];
        }

        $missingFields = array_diff($expectedFields, array_keys($data));
        $extraFields = array_diff(array_keys($data), $expectedFields);

        if (!empty($missingFields)) {
            return ['status' => false, 'message' => 'Missing expected fields: ' . implode(', ', $missingFields)];
        }

        if (!empty($extraFields)) {
            return ['status' => false, 'message' => 'More fields than expected'];
        }

        foreach ($requiredFields as $field) {
            if (empty($data[$field]) || trim($data[$field]) === '') {
                return ['status' => false, 'message' => "Field $field must not be empty"];
            }
        }

        return ['status' => true, 'data' => $data];
    }

    /**
     * Get all cats.
     *
     * Response Codes:
     * 200: List of cats returned successfully.
     */
    #[Route('/cats', name: 'get_cats', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $cats = $this->catRepository->findAll();

        return $this->json($cats);
    }

    /**
     * Get a cat by ID.
     *
     * @param int $id
     * @return JsonResponse
     *
     * Response Codes:
     * 200: Cat details returned successfully.
     * 404: Cat not found.
     */
    #[Route('/cat/{id}', name: 'get_cat_by_id', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $cat = $this->catRepository->find($id);
        if (!$cat) {
            return $this->json(['message' => 'Cat not found'], 404);
        }
        return $this->json($cat);
    }

    /**
     * Update a cat by ID. Route only accessible to ADMIN users.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     *
     * Response Codes:
     * 200: Cat updated successfully.
     * 404: Cat not found.
     */
    #[Route('/admin/update_cat/{id}', name: 'update_cat', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->toArray();

        $cat = $this->entityManager->getRepository(Cat::class)->find($id);

        if (!$cat) {
            return $this->json(['message' => 'Cat not found'], 404);
        }

        foreach ($data as $key => $val) {
            $setter = 'set' . str_replace('_', '', ucwords($key, '_'));


            if (method_exists($cat, $setter)) {
                $value = is_string($val) ? trim($val) : $val;
                $cat->$setter($value);
            }
        }

        return $this->json(["message" => "Cat updated successfully", "data" => [
            "name" => $cat->getName(),
            "short_description" => $cat->getShortDescription(),
            "long_description" => $cat->getLongDescription(),
            "age" => $cat->getAge(),
            "is_vaccinated" => $cat->isVaccinated(),
            "img" => $cat->getImg()
        ]], 200);
    }


    /**
     * Create a new cat. Route only accessible to ADMIN users.
     *
     * Request Body:
     * {
     *   "name": "string",
     *   "short_description": "string",
     *   "long_description": "string",
     *   "age": "integer",
     *   "is_vaccinated": "boolean",
     *   "img": "string"
     * }
     *
     * Response Codes:
     * 201: Cat created successfully.
     * 400: Invalid input data.
     */
    #[Route('/admin/create_cat', name: 'create_cat', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $expectedFields = ["name", "short_description", "long_description", "age", "is_vaccinated", "img"];
        $requiredFields = ["name", "short_description"];

        $validationResult = $this->validateBody($request, $expectedFields, $requiredFields);

        if (!$validationResult['status']) {
            return $this->json(['message' => $validationResult['message']], 400);
        }

        $data = $validationResult['data'];

        foreach ($data as $key => $val) {
            if (is_string($val)) {
                $data[$key] = trim($val);
            }
            if ($val === '') {
                $data[$key] = null;
            }
        }

        $cat = new Cat;
        $cat->setName($data['name'])
            ->setShortDescription($data['short_description'])
            ->setLongDescription($data['long_description'])
            ->setImg($data['img'])
            ->setAge($data['age'])
            ->setIsVaccinated($data['vaccinated']);


        $this->entityManager->persist($cat);
        $this->entityManager->flush();

        return $this->json(["message" => "Cat created successfully", "data" => [
            "name" => $cat->getName(),
            "short_description" => $cat->getShortDescription(),
            "long_description" => $cat->getLongDescription(),
            "img" => $cat->getImg(),
            "age" => $cat->getAge(),
            "is_vaccinated" => $cat->isVaccinated()
        ]], 201);
    }

    /**
     * Delete a cat by ID. Route only accessible to ADMIN users.
     *
     * @param int $id
     * @return JsonResponse
     *
     * Response Codes:
     * 204: Cat deleted successfully.
     * 404: Cat not found.
     */
    #[Route('/admin/delete_cat/{id}', name: 'delete_cat', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $cat = $this->catRepository->find($id);

        if (!$cat) {
            return $this->json(['message' => 'Cat not found'], 404);
        }

        $this->entityManager->remove($cat);
        $this->entityManager->flush();

        return $this->json(["message" => "Cat deleted successfully"], 204);
    }
}
