<?php

namespace App\Service;

use App\Repository\UserRepository;
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

    const TOKEN_VALIDATE = 'https://id.twitch.tv/oauth2/validate';

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
        private readonly string $redirectUri,
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
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

        if($response->getStatusCode() != 400) {
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
        } else {
            return ["access_token" => "Invalid Token"];
        }


    }

    public function revokeToken(string $accessToken): string
    {
        $response = $this->twitchApiClient->request('POST', self::REVOKE_TOKEN_URI, [
            'body' => [
                'client_id' => $this->clientId,
                'token' => $accessToken,
            ]
        ]); 
        if($response->getStatusCode() != 400) {
            return $response->getContent();
        } else {
            return "Invalid Token";
        }
    }

    /**
     * Récupère le token du streamer en BDD
     */
    private function getStreamerAccessToken(string $channelId): string
    {
        $streamer = $this->userRepository->findOneBy(['twitchId' => $channelId]);
        return $streamer->getTwitchAccessToken();
    }

    /**
     * Vérifie la validité du token
     */
    public function validateToken(string $accessToken, string $channelId): string
    {
        $response = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);

        // Si réponse 200, le token est valide donc on le retourne
        if ($response->getStatusCode() === 200) {
            $status = $this->isUserModeratorOrStreamer($accessToken, $channelId);
            if ($status === "moderator") {
                // On récupère le accessToken du streamer en BDD
                $accessToken = $this->getStreamerAccessToken($channelId);
                $response = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $accessToken,
                ]);
                // On vérifie si le token du streamer est valide
                if ($response->getStatusCode() === 200) {
                    return $accessToken;
                } else {
                    // Sinon, on le refresh
                    $response = $this->refreshToken($accessToken);
                    return $response['access_token'];
                }
            }
            return $accessToken;
        } else {
            // Sinon, on le refresh
            $response = $this->refreshToken($accessToken);
            return $response['access_token'];
        }
    }

    /**
     * Informations de l'utilisateur connecté
     * @throws TransportExceptionInterface
     */
    public function fetchUser(string $accessToken)
    {
        $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_USER_ME_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ]
        ]);
        if($response->getStatusCode() != 400 && $response->getStatusCode() != 401) {
            $data = $response->getContent();
            return $this->serializer->decode($data, 'json');
        } else {
            $data = [
                'data' => []
            ];
            array_push($data['data'], ['id' => 1]);
            return $data;
        }
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
     * Check si l'user est soit le streamer, soit un modérateur
     */
    public function isUserModeratorOrStreamer(string $accessToken, string $channelId)
    {
        $moderators = $this->fetchModerators($accessToken, $channelId);
        $channel = $this->fetchChannel($accessToken, $channelId);
        $user = $this->fetchUser($accessToken);

        if ($user['data'][0]['id'] === $channel['broadcaster_id']) {
            return "streamer";
        } else {
            foreach ($moderators as $moderator) {
                if ($moderator['user_id'] === $user['data'][0]['id']) {
                    return "moderator";
                }
            }
        }

        return null;
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
        bool $channelPointsVotingEnabled,
        int $channelPointsPerVote
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
                'duration' => $duration,
                'channel_points_voting_enabled' => $channelPointsVotingEnabled,
                'channel_points_per_vote' => $channelPointsPerVote
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
        string $channelId
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
        $err = [];
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
        if($response->getStatusCode() != 400) {
            $resp = json_decode($response->getContent(), true);
            if (isset($resp['error'])) {
                    array_push($err, $type);
                }
            } else {
                array_push($err, 'Request Error');
            }
        if(count($err)) {
            return ['error_occured' => $err];
        } else {
            return $resp['data'][0];
        }   
    }

    /**
     * Récupérer les données d'une prédiction
     */
    public function getPrediction(
        string $accessToken,
        string $channelId,
    ) {
        $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_PREDICTIONS_ENDPOINT, [
            'auth_bearer' => $accessToken,
            'headers' => [
                'Client-Id' => $this->clientId,
            ],
            'query' => [
                'broadcaster_id' => $channelId,
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
        string $sessionId,
        array $type,
        string $transport
    ) {
        $err = [];
        foreach ($type as $topics => $params) {
            $version = $params['version'];
            $condition = $params['condition'];
            $response = $this->twitchApiClient->request(Request::METHOD_POST, self::TWITCH_EVENTSUB_ENDPOINT, [
                'auth_bearer' => $accessToken,
                'headers' => [
                    'Client-Id' => $this->clientId,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'type' => $topics,
                    'version' => $version,
                    'condition' => $condition,
                    'transport' => [
                        'method' => 'websocket',
                        'session_id' => $sessionId
                    ]
                ])
            ]);
            if($response->getStatusCode() != 400) {
            $resp = json_decode($response->getContent(), true);
                if (isset($resp['error'])) {
                    array_push($err, $type);
                }
            } else {
                array_push($err, 'Request Error');
            }
        }
        if(count($err)) {
            return ['error_occured' => $err];
        } else {
            return ['listener_created' => true];
        }
    }

}