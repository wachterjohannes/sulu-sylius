<?php

namespace App\Controller;

use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

    public function addToCartAction(string $code)
    {
        $token = $this->createCart();

        $client = new Client(['base_uri' => $this->getParameter('app.sylius_url')]);
        $client->post(
            '/shop-api/carts/' . $token . '/items',
            [
                'body' => json_encode(
                    [
                        'productCode' => $code,
                        'quantity' => 3,
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
