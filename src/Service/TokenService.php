<?php

namespace App\Service;


use Symfony\Component\Uid\Uuid;

class TokenService
{

    public function __construct()
    {
        $this->token = null;
    }

    public function generateToken(): string
    {
        $this->token = Uuid::v4();
        return $this->token;
    }

    public function translateTokenFromCookie($data): string
    {
        $matches = array();
        $t = preg_match('/=(.*?);/s', $data, $matches);
        $this->token = $matches[1];
        return $this->token;

        $data = $request->headers->get('set-cookie');
        $matches = array();
        $t = preg_match('/=(.*?);/s', $data, $matches);
        $this->token = $matches[1];
    }
}