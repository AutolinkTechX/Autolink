<?php
namespace App\Controller;

use App\Service\GPTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    private $gptService;

    public function __construct(GPTService $gptService)
    {
        $this->gptService = $gptService;
    }

    #[Route('/chat', name: 'chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $question = $data['question'] ?? '';

        if (!$question) {
            return new JsonResponse(['error' => 'Question non fournie'], 400);
        }

        $response = $this->gptService->getResponse($question);

        return new JsonResponse(['response' => $response]);
    }


    #[Route('/chat-ui', name: 'chat_ui')]
public function chatUI()
{
    return $this->render('chat/chat.html.twig');
}


/*#[Route('/chat', name: 'chat', methods: ['POST'])]
public function chatUI(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    return $this->json(['response' => 'Je suis ton assistant de recyclage']);
}
*/
}
