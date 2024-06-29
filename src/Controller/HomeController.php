<?php

namespace Reelz222z\Cryptoexchange\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    public function index(Request $request)
    {
        return new Response(
            '<html><body>Welcome to Crypto Web App<br><a href="/login">Login</a></body></html>'
        );
    }
}
