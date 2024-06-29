<?php

namespace Reelz222z\Cryptoexchange\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Reelz222z\Cryptoexchange\Model\Login;

class AuthController
{
    public function login(Request $request)
    {
        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');

            if (empty($username) || empty($password)) {
                throw new \Exception('Username or password not provided');
            }

            $user = Login::authenticate($username, $password);

            if (!$user) {
                return new Response('Invalid username or password', 400);
            }

            return $user;
        }

        return new Response('Invalid request method', 405);
    }
}
