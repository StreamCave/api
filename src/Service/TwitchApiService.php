<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TwitchApiService {

    const AUTHORIZATION_URI = 'https://id.twitch.tv/oauth2/authorize';

    const TOKEN_URI = 'https://id.twitch.tv/oauth2/token';

    const REVOKE_TOKEN_URI = 'https://id.twitch.tv/oauth2/revoke';

    const TWITCH_USER_ME_ENDPOINT = 'https://api.twitch.tv/helix/users';

    public function __construct(
        private readonly HttpClientInterface $twitchApiClient,
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
        $response = $this->twitchApiClient->request('POST', self::TOKEN_URI, [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode,
                'redirect_uri' => $this->redirectUri,
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
        $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_USER_ME_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ]
        ]);

        $data = $response->getContent();

        return $this->serializer->decode($data, 'json');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function refreshToken(string $refreshToken): array
    {
        $response = $this->twitchApiClient->request('POST', self::TOKEN_URI, [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'redirect_uri' => $this->redirectUri,
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

    public function revokeToken(string $accessToken): string
    {
        $response = $this->twitchApiClient->request('POST', self::REVOKE_TOKEN_URI, [
            'body' => [
                'client_id' => $this->clientId,
                'token' => $accessToken,
            ]
        ]);
        return $response->getContent();
    }
}