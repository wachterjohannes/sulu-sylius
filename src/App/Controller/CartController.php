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

        $content = json_decode(file_get_contents('http://127.0.0.1:8000/shop-api/carts/' . $token), true);

        return $this->render('website/cart.html.twig', ['content' => $content, 'token' => $token]);
    }

    public function addToCartAction(string $code)
    {
        $token = $this->createCart();

        $client = new Client();
        $client->post(
            'http://127.0.0.1:8000/shop-api/carts/' . $token . '/items',
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
            $client = new Client();
            $token = Uuid::uuid4()->toString();
            $client->post(
                'http://127.0.0.1:8000/shop-api/carts/' . $token,
                ['body' => json_encode(['channel' => 'default'])]
            );
            $this->get('session')->set('cart_token', $token);
        }

        return $token;
    }
}
