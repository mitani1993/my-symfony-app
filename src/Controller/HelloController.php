<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    /**
     * @Route("/hello", name="hello")
     */
    public function index(Request $request)
    {
        $data = [
            array('name' => 'Taro', 'age' => 37, 'mail' => 'taro@yamada'),
            array('name' => 'Hanako', 'age' => 29, 'mail' => 'hanako@flower'),
            array('name' => 'Sachiko', 'age' => 43, 'mail' => 'sachiko@happy'),
            array('name' => 'Jiro', 'age' => 18, 'mail' => 'jiro@change'),
        ];

        return $this->render('hello/index.html.twig', [
            'title' => 'Hello',
            'data' => $data,
        ]);
    }
}
