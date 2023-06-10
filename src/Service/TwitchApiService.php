<?php

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
        UserRepository $userRepository,
        JWTTokenManagerInterface $jwtManager,
        JWTEncoderInterface $jwtEncoder,
        ManagerRegistry $doctrine
    )
    {
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
        $this->jwtEncoder = $jwtEncoder;
        $this->doctrine = $doctrine;
    }

    private function translateJwt(Request $request)
    {
        try {
            $decodedToken = $this->jwtEncoder->decode(str_replace('Bearer ', '', $request->headers->get('Authorization')));
            return $decodedToken;
            // Faites quelque chose avec le jeton décodé
        } catch (\Exception $e) {
            // Gérer les erreurs de décodage, par exemple un jeton invalide
        }
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
    public function refreshToken(string $refreshToken)
    {
        if ($refreshToken === null) {
            return 'No refresh token provided';
        }
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
            $user = $this->userRepository->findOneBy(['twitchRefreshToken' => $refreshToken]);
            if ($user) {
                $user->setTwitchAccessToken($data['access_token']);
                $user->setTwitchRefreshToken($data['refresh_token']);
                $user->setTwitchExpiresIn($responseContent['expires_in']);
                $this->doctrine->getManager()->persist($user);
                $this->doctrine->getManager()->flush();
            }
            return $data;
        } else {
            return null;
        }
    }

    public function revokeToken(string $accessToken)
    {
        $response = $this->twitchApiClient->request('POST', self::REVOKE_TOKEN_URI, [
            'body' => [
                'client_id' => $this->clientId,
                'token' => $accessToken,
            ]
        ]);
        return $response;
    }

    /**
     * Récupère le token du streamer en BDD
     */
    private function getStreamerToken(string $channelId): array
    {
        $streamer = $this->userRepository->findOneBy(['twitchId' => $channelId]);
        return [
            'access_token' => $streamer->getTwitchAccessToken(),
            'refresh_token' => $streamer->getTwitchRefreshToken()
        ];
    }

    /**
     * Vérifie la validité du token
     */
    public function validateToken(string $accessToken, string $refreshToken): string
    {
        // On call Twitch pour vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
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
                $responseContent = json_decode($response->getContent(), true);
                $user = $this->userRepository->findOneBy(['twitchRefreshToken' => $refreshToken]);
                if ($user) {
                    $user->setTwitchAccessToken($responseContent['access_token']);
                    $user->setTwitchRefreshToken($responseContent['refresh_token']);
                    $user->setTwitchExpiresIn($responseContent['expires_in']);
                    $this->doctrine->getManager()->persist($user);
                    $this->doctrine->getManager()->flush();
                }
                $accessToken = $responseContent['access_token'];
            } else {
                // Invalid refreshToken
                $accessToken = null;
            }
        }
        return $accessToken;
    }

    /**
     * Informations de l'utilisateur connecté
     * @throws TransportExceptionInterface
     */
    public function fetchUser(string $accessToken)
    {
        $refresh = null;
        // On doit vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
            $refresh = $this->refreshToken($accessToken);
        }
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
    public function fetchChannel(string $accessToken, string $refreshToken, string $channelId)
    {
        $refresh = null;
        // On doit vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
            $accessToken = $this->refreshToken($refreshToken);
            $refresh = $accessToken;
        }
        $streamerToken = $this->getStreamerToken($channelId);
        if ($streamerToken !== null && $accessToken !== null) {
            if ($streamerToken['access_token'] === $accessToken) {
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

                return [
                    'data' => $data['data'],
                    'refresh' => $refresh
                ];
            } else {
                // On doit récupérer le accessToken du streamer en BDD
                // On vérifie sa validité
                $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $streamerToken['access_token'],
                ]);
                if ($validity->getStatusCode() !== 200) {
                    // On refresh le token
                    $streamRefresh = $this->refreshToken($streamerToken['refresh_token']);
                    // On met à jour le token en BDD
                    $streamerDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
                    $streamerDB->setTwitchAccessToken($streamRefresh['access_token']);
                    $streamerDB->setTwitchRefreshToken($streamRefresh['refresh_token']);
                    $streamerDB->setTwitchExpiresIn($streamRefresh['expires_in']);
                    $em = $this->doctrine->getManager();
                    $em->persist($streamerDB);
                    $em->flush();
                    $streamerToken['access_token'] = $streamRefresh['access_token'];
                }
                $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_CHANNEL_ID_ENDPOINT, [
                    'auth_bearer' => $streamerToken['access_token'],
                    'headers' => [
                        'Client-Id' => $this->clientId,
                    ],
                    'query' => [
                        'broadcaster_id' => $channelId
                    ]
                ]);

                $data = json_decode($response->getContent(), true);

                return [
                    'data' => $data['data'],
                    'refresh' => $refresh
                ];
            }
        } else {
            return null;
        }
    }

    /**
     * Renvoie la liste des modérateurs de la chaîne renseignée
     */
    public function fetchModerators(string $accessToken, string $channelId, string $broadcastId)
    {
        // On récupère les données du streamer en BDD
        $streamer = $this->userRepository->findOneBy(['twitchId' => $channelId]);
        // On vérifie la validité du token du streamer
        $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $streamer->getTwitchAccessToken(),
        ]);

        if ($validity->getStatusCode() !== 200) {
            // On refresh le token
            $response = $this->twitchApiClient->request('POST', self::TOKEN_URI, [
                'body' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $streamer->getTwitchRefreshToken(),
                    'redirect_uri' => $this->redirectUri,
                ]
            ]);

            if($response->getStatusCode() != 400) {
                $responseContent = json_decode($response->getContent(), true);
                $streamer->setTwitchAccessToken($responseContent['access_token']);
                $streamer->setTwitchRefreshToken($responseContent['refresh_token']);
                $streamer->setTwitchExpiresIn($responseContent['expires_in']);
                $this->doctrine->getManager()->persist($streamer);
                $this->doctrine->getManager()->flush();
                // On set temporairement le token du streamer pour que le broadcastId puisse faire la requête
                $accessToken = $responseContent['access_token'];
            } else {
                // Invalid refreshToken
                $accessToken = null;
            }
        }

        // On call l'api Twitch pour get les modérateurs de la chaîne
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

        // Je vérifie si l'utilisateur est bien modérateur de la chaîne
        $isModerator = false;
        $broadcastDB = $this->userRepository->findOneBy(['twitchId' => $broadcastId]);
        if($broadcastDB !== null) {
            foreach ($data['data'] as $moderator) {
                if ($moderator['user_id'] === $broadcastDB->getTwitchId()) {
                    $isModerator = true;
                }
            }
        }

        return $isModerator;
    }

    /**
     * Création d'un sondage
     */

    public function createPoll(
        string $accessToken,
        string $refreshToken,
        string $channelId,
        array $choices,
        string $title,
        int $duration,
        bool $channelPointsVotingEnabled,
        int $channelPointsPerVote
    ) {
        $refresh = null;
        // On doit vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
            $accessToken = $this->refreshToken($refreshToken);
            $refresh = $accessToken;
        }
        $streamerToken = $this->getStreamerToken($channelId);
        if ($streamerToken !== null && $accessToken !== null) {
            if ($streamerToken['access_token'] === $accessToken) {
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

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    return [
                        'data' => $data['data'][0],
                        'refresh' => $refresh
                    ];
                } else {
                    return null;
                }
            } else {
                // On doit récupérer le accessToken du streamer en BDD
                $streamerToken = $this->getStreamerToken($channelId);
                // On vérifie sa validité
                $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $streamerToken['access_token'],
                ]);
                if ($validity->getStatusCode() !== 200) {
                    // On refresh le token
                    $streamRefresh = $this->refreshToken($streamerToken['refresh_token']);
                    // On met à jour le token en BDD
                    $streamerDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
                    $streamerDB->setTwitchAccessToken($streamRefresh['access_token']);
                    $streamerDB->setTwitchRefreshToken($streamRefresh['refresh_token']);
                    $streamerDB->setTwitchExpiresIn($streamRefresh['expires_in']);
                    $em = $this->doctrine->getManager();
                    $em->persist($streamerDB);
                    $em->flush();
                    $streamerToken['access_token'] = $streamRefresh['access_token'];

                    // On doit vérifier la validité du token de l'utilisateur
                }
                $response = $this->twitchApiClient->request(Request::METHOD_POST, self::TWITCH_CREATE_POLL_ENDPOINT, [
                    'auth_bearer' => $streamerToken['access_token'],
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

                if ($response->getStatusCode() === 400) {
                    return null;
                }
                $data = json_decode($response->getContent(), true);

                return [
                    'data' => $data['data'],
                    'refresh' => $refresh
                ];
            }
        } else {
            return null;
        }


    }

    /**
     * Récupération des données du sondage
     *
     * On vérifie la validité du accessToken même si c'est un streamer ou un potentiel modérateur
     */
    public function getPoll(
        string $accessToken,
        string $refreshToken,
        string $channelId
    ) {
        // Récupérer le token du streamer
        $streamerToken = $this->getStreamerToken($channelId);
        if ($streamerToken !== null && $accessToken !== null) {
            if ($streamerToken['access_token'] === $accessToken) {
                $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_POLLS_ENDPOINT, [
                    'auth_bearer' => $accessToken,
                    'headers' => [
                        'Client-Id' => $this->clientId,
                    ],
                    'query' => [
                        'broadcaster_id' => $channelId
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    if (count($data['data']) > 0) {
                        return [
                            'data' => $data['data'][0],
//                            'refresh' => $refresh
                        ];
                    } else {
                        return "You don't have any poll right now.";
                    }
                } else {
                    return "You can't create a poll right now.";
                }
            } else {
                // On doit récupérer le accessToken du streamer en BDD
                $streamerToken = $this->getStreamerToken($channelId);
                // On vérifie sa validité
                $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $streamerToken['access_token'],
                ]);
                if ($validity->getStatusCode() !== 200) {
                    // On refresh le token
                    $streamRefresh = $this->refreshToken($streamerToken['refresh_token']);
                    // On met à jour le token en BDD
                    $streamerDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
                    $streamerDB->setTwitchAccessToken($streamRefresh['access_token']);
                    $streamerDB->setTwitchRefreshToken($streamRefresh['refresh_token']);
                    $streamerDB->setTwitchExpiresIn($streamRefresh['expires_in']);
                    $em = $this->doctrine->getManager();
                    $em->persist($streamerDB);
                    $em->flush();
                    $streamerToken['access_token'] = $streamRefresh['access_token'];

                    // On doit vérifier la validité du token de l'utilisateur
                }
                $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_POLLS_ENDPOINT, [
                    'auth_bearer' => $streamerToken['access_token'],
                    'headers' => [
                        'Client-Id' => $this->clientId,
                    ],
                    'query' => [
                        'broadcaster_id' => $channelId
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    if (count($data['data']) > 0) {
                        return [
                            'data' => $data['data'][0],
                            'refresh' => null
                        ];
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Mettre fin à un poll en cours
     */
    public function endPoll(
        string $accessToken,
        string $refreshToken,
        string $channelId,
        string $pollId,
        string $status = 'TERMINATED'
    ) {
        $refresh = null;
        // On doit vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
            $accessToken = $this->refreshToken($refreshToken);
            $refresh = $accessToken;
        }
        $streamerToken = $this->getStreamerToken($channelId);
        if ($streamerToken['access_token'] !== null && $accessToken !== null) {
            if ($streamerToken === $accessToken) {
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

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    return [
                        'data' => $data['data'],
                        'refresh' => $refresh
                    ];
                } else {
                    return "You can't end a poll right now.";
                }
            } else {
                // On doit récupérer le accessToken du streamer en BDD
                $streamerToken = $this->getStreamerToken($channelId);
                // On vérifie sa validité
                $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $streamerToken['access_token'],
                ]);
                if ($validity->getStatusCode() !== 200) {
                    // On refresh le token
                    $streamRefresh = $this->refreshToken($streamerToken['refresh_token']);
                    // On met à jour le token en BDD
                    $streamerDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
                    $streamerDB->setTwitchAccessToken($streamRefresh['access_token']);
                    $streamerDB->setTwitchRefreshToken($streamRefresh['refresh_token']);
                    $streamerDB->setTwitchExpiresIn($streamRefresh['expires_in']);
                    $em = $this->doctrine->getManager();
                    $em->persist($streamerDB);
                    $em->flush();
                    $streamerToken['access_token'] = $streamRefresh['access_token'];

                    // On doit vérifier la validité du token de l'utilisateur
                }
                $response = $this->twitchApiClient->request(Request::METHOD_PATCH, self::TWITCH_POLLS_ENDPOINT, [
                    'auth_bearer' => $streamerToken['access_token'],
                    'headers' => [
                        'Client-Id' => $this->clientId,
                    ],
                    'body' => [
                        'broadcaster_id' => $channelId,
                        'id' => $pollId,
                        'status' => $status
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    return [
                        'data' => $data['data'],
                        'refresh' => $refresh
                    ];
                } else {
                    return false;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Récupérer les données de tous les polls
     */
    function getPolls(
        string $accessToken,
        string $refreshToken,
        string $channelId
    ) {
        $refresh = null;
        // On doit vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
            $accessToken = $this->refreshToken($refreshToken);
            $refresh = $accessToken;
        }
        $streamerToken = $this->getStreamerToken($channelId);
        if ($streamerToken !== null && $accessToken !== null) {
            if ($streamerToken['access_token'] === $accessToken) {
                $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_POLLS_ENDPOINT, [
                    'auth_bearer' => $accessToken,
                    'headers' => [
                        'Client-Id' => $this->clientId,
                    ],
                    'query' => [
                        'broadcaster_id' => $channelId
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    if (count($data['data']) > 0) {
                        return [
                            'data' => $data['data'],
                            'refresh' => $refresh
                        ];
                    } else {
                        return "You don't have any poll right now.";
                    }
                } else {
                    return "You can't create a poll right now.";
                }
            } else {
                // On doit récupérer le accessToken du streamer en BDD
                $streamerToken = $this->getStreamerToken($channelId);
                // On vérifie sa validité
                $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $streamerToken['access_token'],
                ]);
                if ($validity->getStatusCode() !== 200) {
                    // On refresh le token
                    $streamRefresh = $this->refreshToken($streamerToken['refresh_token']);
                    // On met à jour le token en BDD
                    $streamerDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
                    $streamerDB->setTwitchAccessToken($streamRefresh['access_token']);
                    $streamerDB->setTwitchRefreshToken($streamRefresh['refresh_token']);
                    $streamerDB->setTwitchExpiresIn($streamRefresh['expires_in']);
                    $em = $this->doctrine->getManager();
                    $em->persist($streamerDB);
                    $em->flush();
                    $streamerToken['access_token'] = $streamRefresh['access_token'];

                    // On doit vérifier la validité du token de l'utilisateur
                }
                $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_POLLS_ENDPOINT, [
                    'auth_bearer' => $streamerToken['access_token'],
                    'headers' => [
                        'Client-Id' => $this->clientId,
                    ],
                    'query' => [
                        'broadcaster_id' => $channelId
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    if (count($data['data']) > 0) {
                        return [
                            'data' => $data['data'],
                            'refresh' => $refresh
                        ];
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Créer une prédiction
     */
    function createPrediction(
        string $accessToken,
        string $refreshToken,
        string $channelId,
        string $title,
        array $outcomes, // Choix
        int $predictionWindow
    ) {
        $refresh = null;
        // On doit vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
            $accessToken = $this->refreshToken($refreshToken);
            $refresh = $accessToken;
        }
        $streamerToken = $this->getStreamerToken($channelId);
        if ($streamerToken !== null && $accessToken !== null) {
            if ($streamerToken['access_token'] === $accessToken) {
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
                if($response->getStatusCode() != 400 && $response->getStatusCode() != 403) {
                    $resp = json_decode($response->getContent(), true);
                    if (isset($resp['error'])) {
                        array_push($err, $type);
                    }
                } else if ($response->getStatusCode() === 403) {
                    return "You can't create a prediction right now.";
                } else {
                    array_push($err, 'Request Error');
                }
                if(count($err)) {
                    return ['error_occured' => $err];
                } else {
                    return [
                        'data' => $resp['data'],
                        'refresh' => $refresh
                    ];
                }
            } else {
                // On doit récupérer le accessToken du streamer en BDD
                $streamerToken = $this->getStreamerToken($channelId);
                // On vérifie sa validité
                $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $streamerToken['access_token'],
                ]);
                if ($validity->getStatusCode() !== 200) {
                    // On refresh le token
                    $streamRefresh = $this->refreshToken($streamerToken['refresh_token']);
                    // On met à jour le token en BDD
                    $streamerDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
                    $streamerDB->setTwitchAccessToken($streamRefresh['access_token']);
                    $streamerDB->setTwitchRefreshToken($streamRefresh['refresh_token']);
                    $streamerDB->setTwitchExpiresIn($streamRefresh['expires_in']);
                    $em = $this->doctrine->getManager();
                    $em->persist($streamerDB);
                    $em->flush();
                    $streamerToken['access_token'] = $streamRefresh['access_token'];

                    // On doit vérifier la validité du token de l'utilisateur
                }
                $err = [];
                $response = $this->twitchApiClient->request(Request::METHOD_POST, self::TWITCH_PREDICTIONS_ENDPOINT, [
                    'auth_bearer' => $streamerToken['access_token'],
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
                if($response->getStatusCode() != 400 && $response->getStatusCode() != 403) {
                    $resp = json_decode($response->getContent(), true);
                    if (isset($resp['error'])) {
                        array_push($err, $type);
                    }
                } else if ($response->getStatusCode() === 403) {
                    return "You can't create a prediction right now.";
                } else {
                    array_push($err, 'Request Error');
                }
                if(count($err)) {
                    return ['error_occured' => $err];
                } else {
                    return [
                        'data' => $resp['data'],
                        'refresh' => $refresh
                    ];
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Récupérer les données d'une prédiction
     */
    public function getPrediction(
        string $accessToken,
        string $refreshToken,
        string $channelId
    ) {
        $refresh = null;
        // On doit vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
            $accessToken = $this->refreshToken($refreshToken);
            $refresh = $accessToken;
        }
        $streamerToken = $this->getStreamerToken($channelId);
        if ($streamerToken !== null && $accessToken !== null) {
            if ($streamerToken['access_token'] === $accessToken) {
                $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_PREDICTIONS_ENDPOINT, [
                    'auth_bearer' => $accessToken,
                    'headers' => [
                        'Client-Id' => $this->clientId,
                    ],
                    'query' => [
                        'broadcaster_id' => $channelId,
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    if (count($data['data']) > 0) {
                        return [
                            'data' => $data['data'][0],
                            'refresh' => $refresh
                        ];
                    } else {
                        return "You don't have any prediction right now.";
                    }
                } else {
                    return "You can't create a prediction right now.";
                }
            } else {
                // On doit récupérer le accessToken du streamer en BDD
                $streamerToken = $this->getStreamerToken($channelId);
                // On vérifie sa validité
                $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $streamerToken['access_token'],
                ]);
                if ($validity->getStatusCode() !== 200) {
                    // On refresh le token
                    $streamRefresh = $this->refreshToken($streamerToken['refresh_token']);
                    // On met à jour le token en BDD
                    $streamerDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
                    $streamerDB->setTwitchAccessToken($streamRefresh['access_token']);
                    $streamerDB->setTwitchRefreshToken($streamRefresh['refresh_token']);
                    $streamerDB->setTwitchExpiresIn($streamRefresh['expires_in']);
                    $em = $this->doctrine->getManager();
                    $em->persist($streamerDB);
                    $em->flush();
                    $streamerToken['access_token'] = $streamRefresh['access_token'];

                    // On doit vérifier la validité du token de l'utilisateur
                }
                $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_PREDICTIONS_ENDPOINT, [
                    'auth_bearer' => $streamerToken['access_token'],
                    'headers' => [
                        'Client-Id' => $this->clientId,
                    ],
                    'query' => [
                        'broadcaster_id' => $channelId,
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    if (count($data['data']) > 0) {
                        return [
                            'data' => $data['data'][0],
                            'refresh' => $refresh
                        ];
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Mettre fin à une prédiction
     */
    public function endPrediction(
        string $accessToken,
        string $refreshToken,
        string $channelId,
        string $id,
        string $status,
        string $winningOutcomeId = null
    ) {
        $refresh = null;
        // On doit vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
            $accessToken = $this->refreshToken($refreshToken);
            $refresh = $accessToken;
        }
        $streamerToken = $this->getStreamerToken($channelId);
        if ($streamerToken !== null && $accessToken !== null) {
            if ($streamerToken['access_token'] === $accessToken) {
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

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    return [
                        'data' => $data['data'],
                        'refresh' => $refresh
                    ];
                } else {
                    return "You can't end a prediction right now.";
                }
            } else {
                // On doit récupérer le accessToken du streamer en BDD
                $streamerToken = $this->getStreamerToken($channelId);
                // On vérifie sa validité
                $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $streamerToken['access_token'],
                ]);
                if ($validity->getStatusCode() !== 200) {
                    // On refresh le token
                    $streamRefresh = $this->refreshToken($streamerToken['refresh_token']);
                    // On met à jour le token en BDD
                    $streamerDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
                    $streamerDB->setTwitchAccessToken($streamRefresh['access_token']);
                    $streamerDB->setTwitchRefreshToken($streamRefresh['refresh_token']);
                    $streamerDB->setTwitchExpiresIn($streamRefresh['expires_in']);
                    $em = $this->doctrine->getManager();
                    $em->persist($streamerDB);
                    $em->flush();
                    $streamerToken['access_token'] = $streamRefresh['access_token'];

                    // On doit vérifier la validité du token de l'utilisateur
                }
                $response = $this->twitchApiClient->request(Request::METHOD_PATCH, self::TWITCH_PREDICTIONS_ENDPOINT, [
                    'auth_bearer' => $streamerToken['access_token'],
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

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    return [
                        'data' => $data['data'],
                        'refresh' => $refresh
                    ];
                } else {
                    return false;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Récupérer toutes les prédictions
     */
    public function getAllPrediction(
        string $accessToken,
        string $refreshToken,
        string $channelId
    ) {
        $refresh = null;
        // On doit vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
            $accessToken = $this->refreshToken($refreshToken);
            $refresh = $accessToken;
        }
        $streamerToken = $this->getStreamerToken($channelId);
        if ($streamerToken !== null && $accessToken !== null) {
            if ($streamerToken['access_token'] === $accessToken) {
                $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_PREDICTIONS_ENDPOINT, [
                    'auth_bearer' => $accessToken,
                    'headers' => [
                        'Client-Id' => $this->clientId,
                    ],
                    'query' => [
                        'broadcaster_id' => $channelId
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    if (count($data['data']) > 0) {
                        return [
                            'data' => $data['data'],
                            'refresh' => $refresh
                        ];
                    } else {
                        return "You don't have any prediction right now.";
                    }
                } else {
                    return "You can't create a prediction right now.";
                }
            } else {
                // On doit récupérer le accessToken du streamer en BDD
                $streamerToken = $this->getStreamerToken($channelId);
                // On vérifie sa validité
                $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $streamerToken['access_token'],
                ]);
                if ($validity->getStatusCode() !== 200) {
                    // On refresh le token
                    $streamRefresh = $this->refreshToken($streamerToken['refresh_token']);
                    // On met à jour le token en BDD
                    $streamerDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
                    $streamerDB->setTwitchAccessToken($streamRefresh['access_token']);
                    $streamerDB->setTwitchRefreshToken($streamRefresh['refresh_token']);
                    $streamerDB->setTwitchExpiresIn($streamRefresh['expires_in']);
                    $em = $this->doctrine->getManager();
                    $em->persist($streamerDB);
                    $em->flush();
                    $streamerToken['access_token'] = $streamRefresh['access_token'];

                    // On doit vérifier la validité du token de l'utilisateur
                }
                $response = $this->twitchApiClient->request(Request::METHOD_GET, self::TWITCH_PREDICTIONS_ENDPOINT, [
                    'auth_bearer' => $streamerToken['access_token'],
                    'headers' => [
                        'Client-Id' => $this->clientId,
                    ],
                    'query' => [
                        'broadcaster_id' => $channelId
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(), true);
                    if (count($data['data']) > 0) {
                        return [
                            'data' => $data['data'],
                            'refresh' => $refresh
                        ];
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Create EventSub Subscription
     */
    public function createEventSubSubscription(
        string $accessToken,
        string $refreshToken,
        string $sessionId,
        string $channelId,
        array $type,
        string $transport
    ) {
        $refresh = null;
        // On doit vérifier la validité du token
        $validityUser = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
            'auth_bearer' => $accessToken,
        ]);
        if ($validityUser->getStatusCode() != 200) {
            // On refresh le token
            $accessToken = $this->refreshToken($refreshToken);
            $refresh = $accessToken;
        }
        $streamerToken = $this->getStreamerToken($channelId);
        if ($streamerToken !== null && $accessToken !== null) {
            if ($streamerToken['access_token'] === $accessToken) {
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
                    return [
                        'listener_created' => true,
                        'refresh' => $refresh
                    ];
                }
            } else {
                // On doit récupérer le accessToken du streamer en BDD
                $streamerToken = $this->getStreamerToken($channelId);
                // On vérifie sa validité
                $validity = $this->twitchApiClient->request('GET', self::TOKEN_VALIDATE, [
                    'auth_bearer' => $streamerToken['access_token'],
                ]);
                if ($validity->getStatusCode() !== 200) {
                    // On refresh le token
                    $streamRefresh = $this->refreshToken($streamerToken['refresh_token']);
                    // On met à jour le token en BDD
                    $streamerDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
                    $streamerDB->setTwitchAccessToken($streamRefresh['access_token']);
                    $streamerDB->setTwitchRefreshToken($streamRefresh['refresh_token']);
                    $streamerDB->setTwitchExpiresIn($streamRefresh['expires_in']);
                    $em = $this->doctrine->getManager();
                    $em->persist($streamerDB);
                    $em->flush();
                    $streamerToken['access_token'] = $streamRefresh['access_token'];

                    // On doit vérifier la validité du token de l'utilisateur
                }
                $err = [];
                foreach ($type as $topics => $params) {
                    $version = $params['version'];
                    $condition = $params['condition'];
                    $response = $this->twitchApiClient->request(Request::METHOD_POST, self::TWITCH_EVENTSUB_ENDPOINT, [
                        'auth_bearer' => $streamerToken['access_token'],
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
                    return [
                        'listener_created' => true,
                        'refresh' => $refresh
                    ];
                }
            }
        } else {
            return null;
        }
    }

}