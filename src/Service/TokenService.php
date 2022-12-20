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
        if($data) {
            return $this->token = $data;
        }
        return 'Invalid Token';
        
        // if(preg_match('/refresh_token', $data)) {
        //     $matches = array();
        //     $t = preg_match('/=(.*?);/s', $data, $matches);
        //     $this->token = $matches[1];
        //     return $this->token;
        // } else {
        //     return $this->token = $data;
        // }

    }
}