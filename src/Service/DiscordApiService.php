<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordApiService {

    const AUTHORIZATION_URI = 'https://discord.com/api/oauth2/authorize';
    const TOKEN_URI = 'https://discord.com/api/oauth2/token';
    const USERS_ME_ENDPOINT = '/api/users/@me';

    public function __construct(
        private readonly HttpClientInterface $discordApiClient,
        private readonly SerializerInterface $serializer,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri
    )
    {
    }

    public function getAuthorizationUri(array $scope): string
    {
        $queryParameters = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scope)
        ]);

        return self::AUTHORIZATION_URI . '?' . $queryParameters;
    }

    public function getAccessToken(string $authorizationCode): array
    {
        $response = $this->discordApiClient->request('POST', self::TOKEN_URI, [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode,
                'redirect_uri' => $this->redirectUri,
                'scope' => 'identify email',
                'access_type' => 'offline'
            ]
        ]);

        $responseContent = $response->getContent();
        $responseContent = $this->serializer->decode($responseContent, 'json');

        $data = [
            'access_token' => $responseContent['access_token'],
            'refresh_token' => $responseContent['refresh_token'],
            'expires_in' => $responseContent['expires_in'],
            'token_type' => $responseContent['token_type'],
            'scope' => $responseContent['scope']
        ];

        return $data;
    }

    public function fetchUser(string $accessToken)
    {
        $response = $this->discordApiClient->request(Request::METHOD_GET, self::USERS_ME_ENDPOINT, [
            'auth_bearer' => $accessToken
        ]);

        $data = $response->getContent();

        return $this->serializer->decode($data, 'json');
    }
}