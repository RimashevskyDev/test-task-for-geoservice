<?php

namespace App\Controller;

use App\Form\MainFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(MainFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('app_check', ['zoom' => $form->get('zoom')->getData(), 'x' => $form->get('x')->getData(), 'y' => $form->get('y')->getData()]);
        }

        return $this->render('main/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/get/{zoom}&{x}&{y}', name: 'app_check')]
    public function visible(mixed $zoom = NULL, mixed $x = NULL, mixed $y = NULL): Response
    {
        /* Можно было через цикл */
        if (!is_numeric($zoom) || !is_numeric($x) || !is_numeric($y)) {
            $this->addFlash('error', 'Введите корректное значение!');
            return $this->redirectToRoute('app_main');
        }

        $coordinates = $this->calculateCoordinates($x, $y, $zoom);

        return $this->render('main/visible.html.twig', [
            'zoom' => $zoom,
            'x' => $x,
            'y' => $y,
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'iframe' => 'https://core-carparks-renderer-lots.maps.yandex.net/maps-rdr-carparks/tiles?l=carparks&x=' . $x . '&y=' . $y . '&z=' . $zoom . '&scale=1&lang=ru_RU'
        ]);
    }

    /**
     * @param int $tileX
     * @param int $tileY
     * @param int $zoom
     * @return array
     */
    public function calculateCoordinates(int $tileX, int $tileY, int $zoom): array
    {
        $n = pow(2, $zoom);

        $lonDeg = $tileX / $n * 360.0 - 180.0;
        $latDeg = rad2deg(atan(sinh(pi() * (1 - 2 * $tileY / $n))));

        return [
            'latitude' => $latDeg,
            'longitude' => $lonDeg
        ];
    }
}

