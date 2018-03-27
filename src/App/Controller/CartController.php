<?php

namespace App\Controller;

use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CartController extends Controller
{
    public function indexAction()
    {
        $token = $this->createCart();

        $baseUri = $this->getParameter('app.sylius_url');
        $client = new Client(['base_uri' => $baseUri]);
        $response = $client->get('/shop-api/carts/' . $token);
        $content = json_decode($response->getBody(), true);

        return $this->render(
            'website/cart.html.twig',
            [
                'content' => $content,
                'token' => $token,
                'baseUri' => $baseUri,
            ]
        );
    }

    public function completedAction()
    {
        $this->get('session')->remove('cart_token');

        return $this->render('website/complete.html.twig');
    }

    public function addToCartAction(string $code, Request $request)
    {
        $token = $this->createCart();

        $client = new Client(['base_uri' => $this->getParameter('app.sylius_url')]);
        $client->post(
            '/shop-api/carts/' . $token . '/items',
            [
                'body' => json_encode(
                    [
                        'productCode' => $code,
                        'variantCode' => $request->get('variant'),
                        'quantity' => 1,
                    ]
                )
            ]
        );

        return $this->redirectToRoute('app_cart');
    }

    private function createCart()
    {
        $token = $this->get('session')->get('cart_token');

        if (!$token) {
            $client = new Client(['base_uri' => $this->getParameter('app.sylius_url')]);
            $token = Uuid::uuid4()->toString();
            $client->post(
                '/shop-api/carts/' . $token,
                ['body' => json_encode(['channel' => $this->getParameter('app.sylius_channel')])]
            );
            $this->get('session')->set('cart_token', $token);
        }

        return $token;
    }
}
