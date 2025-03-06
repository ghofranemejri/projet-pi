<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/api/medicament', name: 'api_medicament', methods: ['GET'])]
    public function getMedicamentDetails(Request $request): JsonResponse
    {
        $medicament = $request->query->get('name');

        if (!$medicament) {
            return $this->json(['error' => 'Missing medicine name'], 400);
        }

        $apiUrl = "https://api.fda.gov/drug/label.json?search=openfda.brand_name:$medicament&limit=1";

        try {
            $response = $this->httpClient->request('GET', $apiUrl);
            $data = $response->toArray();

            if (!isset($data['results'][0])) {
                return $this->json(['error' => 'No data found'], 404);
            }

            $result = $data['results'][0];

            return $this->json([
                'medicament' => $medicament,
                'description' => $result['description'][0] ?? 'No description available',
                'dosage' => $result['dosage_and_administration'][0] ?? 'No dosage information',
                'side_effects' => $result['adverse_reactions'][0] ?? 'No side effects information',
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'API request failed', 'message' => $e->getMessage()], 500);
        }
    }
}
