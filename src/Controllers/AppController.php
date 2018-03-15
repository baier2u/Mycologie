<?php

namespace App\Controllers;

use App\Models\Specie;
use App\Models\User;
use App\Utils\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppController extends BaseController
{

    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $species_recent = Specie::limit(5)
            ->orderBy('created_at', 'DESC')
            ->get();

        $species_month_recent = Specie::whereBetween('created_at', [date('Y-m-1'), date('Y-m-t')])
            ->get();

        $this->render($response, 'app/index', [
            'species_recent' => $species_recent,
            'species_month_recent' => $species_month_recent
        ]);
    }

}