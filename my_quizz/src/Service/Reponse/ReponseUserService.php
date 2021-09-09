<?php

namespace App\Service\Reponse;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ReponseUserService
{
    protected $session;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    public function addResponse($id, $idQuestion)
    {
        $stockReponses = $this->session->get('stockReponses', []);

        $stockReponses[$idQuestion] = ["idReponse" => $id];

        $this->session->set('stockReponses', $stockReponses);
    }

    public function getResponses() {
        return $this->session->get('stockReponses');
    }

    public function removeResponses()
    {
        $this->session->remove('stockReponses');
    }
}