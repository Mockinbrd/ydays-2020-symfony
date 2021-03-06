<?php

namespace App\Controller;

use App\Client\CoinGeckoClient;
use App\Entity\Purchase;
use App\Entity\User;
use App\Form\PurchaseType;
use App\Service\PurchaseHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PurchaseController
 * @package App\Controller
 */
class PurchaseController extends AbstractController
{
    /**
     * @Route("/purchase/new", name="purchase_new")
     */
    public function new(CoinGeckoClient $coinGeckoClient, Request $request, PurchaseHandler $purchaseHandler): Response
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('isVerifiedCheck',$user);

        $form = $this->createForm(PurchaseType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            try {
                $purchaseHandler->handle($request->request->get('purchase'), $user);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('purchase_new');
            }
        }
        $coins = $coinGeckoClient->list();
        return $this->render('purchase/new.html.twig', [
            'coins' => json_encode($coins),
            'form' => $form->createView()
        ]);
    }
}
