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

    const TWITCH_CHANNEL_ID_ENDPOINT = 'https://api.twitch.tv/helix/channels';

    const TWITCH_MODERATORS_ENPOINT = 'https://api.twitch.tv/helix/moderation/moderators';

    const TWITCH_CREATE_POLL_ENDPOINT = 'https://api.twitch.tv/helix/polls';

    const TWITCH_POLLS_ENDPOINT = 'https://api.twitch.tv/helix/polls';

    const TWITCH_PREDICTIONS_ENDPOINT = 'https://api.twitch.tv/helix/predictions';

    const TWITCH_EVENTSUB_ENDPOINT = 'https://api.twitch.tv/helix/eventsub/subscriptions';

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

    /**
     * Informations de l'utilisateur connecté
     */
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
     * Informations de la chaîne de l'utilisateur renseigné
     */
    public function fetchChannel(string $accessToken, string $channelId)
    {
        if ($channelId !== null) {
            $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_CHANNEL_ID_ENDPOINT, [
                'auth_bearer' => $accessToken,
                'headers' => [
                    'Client-Id' => $this->clientId,
                ],
                'query' => [
                    'broadcaster_id' => $channelId
                ]
            ]);

            $data = json_decode($response->getContent(), true);

            return $data['data'][0];
        } else {
            return null;
        }
    }

    /**
     * Renvoie la liste des modérateurs de la chaîne renseignée
     */
    public function fetchModerators(string $accessToken, string $channelId)
    {
        if ($channelId !== null) {
            $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_MODERATORS_ENPOINT, [
                'auth_bearer' => $accessToken,
                'headers' => [
                    'Client-Id' => $this->clientId,
                ],
                'query' => [
                    'broadcaster_id' => $channelId
                ]
            ]);

            $data = json_decode($response->getContent(), true);

            return $data['data'];
        } else {
            return null;
        }
    }

    /**
     * Création d'un sondage
     */

    public function createPoll(
        string $accessToken,
        string $channelId,
        array $choices,
        string $title,
        int $duration,
        bool $channelPointsVotingEnabled = false,
        int $channelPointsPerVote = 1
    ) {
        $response = $this->twitchApiClient->request(Request::METHOD_POST, self::TWITCH_CREATE_POLL_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ],
            'body' => [
                'broadcaster_id' => $channelId,
                'title' => $title,
                'choices' => $choices,
                'duration' => $duration
            ]
        ]);

        $data = json_decode($response->getContent(), true);

        return $data['data'][0];
    }

    /**
     * Récupération des données du sondage
     */
    public function getPoll(
        string $accessToken,
        string $channelId,
        string $pollId
    ) {
        $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_POLLS_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ],
            'query' => [
                'broadcaster_id' => $channelId,
                'id' => $pollId
            ]
        ]);

        $data = json_decode($response->getContent(), true);

        return $data['data'][0];
    }

    /**
     * Mettre fin à un poll en cours
     */
    public function endPoll(
        string $accessToken,
        string $channelId,
        string $pollId,
        string $status = 'TERMINATED'
    ) {
        $response = $this->twitchApiClient->request(Request::METHOD_PATCH, self::TWITCH_POLLS_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ],
            'body' => [
                'broadcaster_id' => $channelId,
                'id' => $pollId,
                'status' => $status
            ]
        ]);

        $data = json_decode($response->getContent(), true);

        return $data['data'][0];
    }

    /**
     * Récupérer les données de tous les polls
     */
    function getPolls(
        string $accessToken,
        string $channelId,
    ) {
        $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_POLLS_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ],
            'query' => [
                'broadcaster_id' => $channelId
            ]
        ]);

        $data = json_decode($response->getContent(), true);

        return $data['data'][0];
    }

    /**
     * Créer une prédiction
     */
    function createPrediction(
        string $accessToken,
        string $channelId,
        string $title,
        array $outcomes, // Choix
        int $predictionWindow
    ) {
        $response = $this->twitchApiClient->request(Request::METHOD_POST, self::TWITCH_PREDICTIONS_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ],
            'body' => [
                'broadcaster_id' => $channelId,
                'title' => $title,
                'outcomes' => $outcomes,
                'prediction_window' => $predictionWindow
            ]
        ]);

        $data = json_decode($response->getContent(), true);

        return $data['data'][0];
    }

    /**
     * Récupérer les données d'une prédiction
     */
    public function getPrediction(
        string $accessToken,
        string $channelId,
        string $id
    ) {
        $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_PREDICTIONS_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ],
            'query' => [
                'broadcaster_id' => $channelId,
                'id' => $id
            ]
        ]);

        $data = json_decode($response->getContent(), true);

        return $data['data'][0];
    }

    /**
     * Mettre fin à une prédiction
     */
    public function endPrediction(
        string $accessToken,
        string $channelId,
        string $id,
        string $status,
        string $winningOutcomeId = null
    ) {
        $response = $this->twitchApiClient->request(Request::METHOD_PATCH, self::TWITCH_PREDICTIONS_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ],
            'body' => [
                'broadcaster_id' => $channelId,
                'id' => $id,
                'status' => $status,
                'winning_outcome_id' => $winningOutcomeId
            ]
        ]);

        $data = json_decode($response->getContent(), true);

        return $data['data'][0];
    }

    /**
     * Récupérer toutes les prédictions
     */
    public function getAllPrediction(
        string $accessToken,
        string $channelId
    ) {
        $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_PREDICTIONS_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ],
            'query' => [
                'broadcaster_id' => $channelId
            ]
        ]);

        $data = json_decode($response->getContent(), true);

        return $data['data'][0];
    }

    /**
     * Create EventSub Subscription
     */
    public function createEventSubSubscription(
        string $accessToken,
        string $type,
        string $version,
        object $condition,
        object $transport
    ) {
        $response = $this->twitchApiClient->request(Request::METHOD_POST, self::TWITCH_EVENTSUB_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
                'Content-Type' => 'application/json'
            ],
            'body' => [
                'type' => $type,
                'version' => $version,
                'condition' => $condition,
                'transport' => $transport
            ]
        ]);

        $data = json_decode($response->getContent(), true);

        return $data['data'][0];
    }

}