<?php

namespace AppBundle\Controller\App;

use AppBundle\Security\User\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LibraryController extends Controller
{
    public function cssAction($name, $minified, Request $request)
    {
        var_dump(__METHOD__, $name, $minified);
        die();
    }

    public function jsAction($name, $minified, Request $request)
    {
        var_dump(__METHOD__, $name, $minified);
        die();
    }
}
