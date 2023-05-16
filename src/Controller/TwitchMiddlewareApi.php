<?php

namespace App\Controller;

use App\Service\TwitchApiService;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/middleware/twitch', methods: ['POST'])]
class TwitchMiddlewareApi extends AbstractController {

    public function __construct(TwitchApiService $twitchApiService, JWTEncoderInterface $jwtEncoder)
    {
        $this->twitchApiService = $twitchApiService;
        $this->jwtEncoder = $jwtEncoder;
    }

    private function decodeData(Request $request) {
        return json_decode($request->getContent(), true);
    }

    private function getJwt(Request $request) {
        return str_replace('Bearer ', '', $request->headers->get('Authorization'));
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

    #[Route('/user', name: 'twitch_user', methods: ['POST'])]
    public function getUserInfo(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $refreshToken = $data['refresh_token'] ?? array_push($err, 'refresh_token');
        if (count($err) === 0) {
            $userTwitch = $this->twitchApiService->fetchUser($accessToken);
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'user' => $userTwitch
                ],
                200,
            );
            if ($userTwitch['data'][0]['id'] == $this->translateJwt($request)['twitchId']) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $this->getJwt($request),
                        new \DateTime('+1 day'),
                        '/',
                        'localhost',
                        true,
                        true,
                        false,
                        'none'
                    ));
            }
            return $finalResponse;
        } else {
            return new JsonResponse([
                'statusCode' => 400,
                'message' => 'Missing or invalid parameters',
                'missing_parameters' => $err
            ], 400);
        }
    }

    // INFO: Get channel info si t'es streamer OK
    // INFO: Get channel info si t'es pas streamer WAIT
    #[Route('/channel', name: 'twitch_channel', methods: ['POST'])]
    public function getChannel(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $refreshToken = $data['refresh_token'] ?? array_push($err, 'refresh_token');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
        if (count($err) === 0) {
            $channel = $this->twitchApiService->fetchChannel($accessToken, $channelId);
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'channelId' => $channel
                ],
                200,
            );
            if ($channelId == $this->translateJwt($request)['twitchId']) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $this->getJwt($request),
                        new \DateTime('+1 day'),
                        '/',
                        'localhost',
                        true,
                        true,
                        false,
                        'none'
                    ));
            }
            return $finalResponse;
        } else {
            return new JsonResponse([
                'statusCode' => 400,
                'message' => 'Missing or invalid parameters',
                'missing_parameters' => $err
            ], 400);
        }
    }

    // INFO: Get channel moderators si t'es streamer OK
    // INFO: Get channel moderators si t'es pas streamer OK
    #[Route('/moderators', name: 'twitch_moderators', methods: ['POST'])]
    public function getModerators(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $refreshToken = $data['refresh_token'] ?? array_push($err, 'refresh_token');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
        if (count($err) === 0) {
            $userUuid = $this->translateJwt($request)['uuid'];
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId, $userUuid);
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'moderators' => $moderators
                ],
                200,
            );
            if ($channelId == $this->translateJwt($request)['twitchId']) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $accessToken,
                        new \DateTime('+1 day'),
                        '/',
                        'localhost',
                        true,
                        true,
                        false,
                        'none'
                    ));
            }
            return $finalResponse;
        } else {
            return new JsonResponse([
                'statusCode' => 400,
                'message' => 'Missing or invalid parameters',
                'missing_parameters' => $err
            ], 400);
        }
    }

    /**
     * @param Request $request
     *
     * Check si l'user est bien modérateur du channel
     */
    #[Route('/check-access', name: 'twitch_check_access', methods: ['POST'])]
    public function checkAccessChannel(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $refreshToken = $data['refresh_token'] ?? array_push($err, 'refresh_token');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
        if (count($err) === 0) {
            $accessToken = $this->twitchApiService->validateToken($request, $data['access_token'], $data['refresh_token'], $channelId);

            $isUserModeratorOrStreamer = $this->twitchApiService->isUserModeratorOrStreamer($accessToken, $channelId);

            if ($isUserModeratorOrStreamer != null) {
                if ($channelId == $this->translateJwt($request)['twitchId']) {
                    $finalResponse = new JsonResponse(
                        [
                            'statusCode' => 200,
                            'isUserModeratorOrStreamer' => $isUserModeratorOrStreamer
                        ],
                        200,
                    );
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $this->getJwt($request),
                            new \DateTime('+1 day'),
                            '/',
                            'localhost',
                            true,
                            true,
                            false,
                            'none'
                        ));
                }
                return $finalResponse;
            } else {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'User is not moderator or streamer'
                ], 400);
            }
        } else {
            return new JsonResponse([
                'statusCode' => 400,
                'message' => 'Missing or invalid parameters',
                'missing_parameters' => $err
            ], 400);
        }
    }

    /**
     * @param Request $request
     *
     * Créer un poll
     */
    #[Route('/poll/create', name: 'twitch_poll_create', methods: ['POST'])]
    public function createPoll(Request $request): JsonResponse
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk->getStatusCode() === 200) {
            $data = $this->decodeData($request);
            $err = [];
            $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            $title = $data['title'] ?? array_push($err, 'title');
            $choices = $data['choices'] ?? array_push($err, 'choices');
            $duration = $data['duration'] ?? array_push($err, 'duration');
            $channelPointsVotingEnabled = $data['channel_points_voting_enabled'] ?? array_push($err, 'channel_points_voting_enabled');
            $channelPointsVotingEnabled = $channelPointsVotingEnabled === true ? true : false;
            $channelPointsPerVote = 1;
            if($channelPointsVotingEnabled === true) {
                $channelPointsPerVote = $data['channel_points_per_vote'] ?? array_push($err, 'channel_points_per_vote');
            }
            if (count($err) == 0) {
                $response = $this->twitchApiService->createPoll($accessToken, $channelId, $choices, $title, $duration, $channelPointsVotingEnabled, $channelPointsPerVote);
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'response' => $response
                    ],
                    200,
                );
                if ($channelId == $this->translateJwt($request)['twitchId']) {
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $this->getJwt($request),
                            new \DateTime('+1 day'),
                            '/',
                            'localhost',
                            true,
                            true,
                            false,
                            'none'
                        ));
                }
                return $finalResponse;
            } else {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'Missing or invalid parameters',
                    'missing_parameters' => $err
                ], 400);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     *
     * Récupérer les données du poll
     */
    #[Route('/poll/get', name: 'twitch_poll_get', methods: ['POST'])]
    public function getPoll(Request $request) : JsonResponse
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk->getStatusCode() === 200) {
            $data = $this->decodeData($request);
            $err = [];
            $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            if (count($err) == 0) {
                $accessToken = $this->twitchApiService->validateToken($request, $data['access_token'], $data['refresh_token'], $channelId);
                $response = $this->twitchApiService->getPoll($accessToken, $channelId);
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'response' => $response
                    ],
                    200,
                );
                if ($channelId == $this->translateJwt($request)['twitchId']) {
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $this->getJwt($request),
                            new \DateTime('+1 day'),
                            '/',
                            'localhost',
                            true,
                            true,
                            false,
                            'none'
                        ));
                }
                return $finalResponse;
            } else {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'Missing or invalid parameters',
                    'missing_parameters' => $err
                ], 400);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     *
     * Mettre fin à un poll en cours
     */
    #[Route('/poll/end', name: 'twitch_poll_end', methods: ['POST'])]
    public function endPoll(Request $request): JsonResponse
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            $id = $data['id'] ?? array_push($err, 'id');
            $status = $data['status'] ?? 'TERMINATED';
            if (count($err) == 0) {
                $accessToken = $this->twitchApiService->validateToken($request, $data['access_token'], $data['refresh_token'], $channelId);
                $response = $this->twitchApiService->endPoll($accessToken, $channelId, $id, $status);
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'response' => $response
                    ],
                    200,
                );
                if ($channelId == $this->translateJwt($request)['twitchId']) {
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $this->getJwt($request),
                            new \DateTime('+1 day'),
                            '/',
                            'localhost',
                            true,
                            true,
                            false,
                            'none'
                        ));
                }
                return $finalResponse;
            } else {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'Missing or invalid parameters',
                    'missing_parameters' => $err
                ], 400);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     *
     * Récupérer tous les polls
     */
    #[Route('/poll/all', name: 'twitch_poll_all', methods: ['POST'])]
    public function getAllPoll(Request $request): JsonResponse
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            if (count($err) == 0) {
                $accessToken = $this->twitchApiService->validateToken($request, $data['access_token'], $data['refresh_token'], $channelId);
                $response = $this->twitchApiService->getPolls($accessToken, $channelId);
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'response' => $response
                    ],
                    200,
                );
                if ($channelId == $this->translateJwt($request)['twitchId']) {
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $this->getJwt($request),
                            new \DateTime('+1 day'),
                            '/',
                            'localhost',
                            true,
                            true,
                            false,
                            'none'
                        ));
                }
                return $finalResponse;
            } else {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'Missing or invalid parameters',
                    'missing_parameters' => $err
                ], 400);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     *
     * Créer une prédiction
     */
    #[Route('/prediction/create', name: 'twitch_prediction_create', methods: ['POST'])]
    public function createPrediction(Request $request): JsonResponse
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            $title = $data['title'] ?? array_push($err, 'title');
            $outcomes = $data['outcomes'] ?? array_push($err, 'outcomes');
            $predictionWindow = $data['predictionWindow'] ?? array_push($err, 'predictionWindow');
            if (count($err) == 0) {
                $accessToken = $this->twitchApiService->validateToken($request, $data['access_token'], $data['refresh_token'], $channelId);
                $response = $this->twitchApiService->createPrediction($accessToken, $channelId, $title, $outcomes, $predictionWindow);
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'response' => $response
                    ],
                    200,
                );
                if ($channelId == $this->translateJwt($request)['twitchId']) {
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $this->getJwt($request),
                            new \DateTime('+1 day'),
                            '/',
                            'localhost',
                            true,
                            true,
                            false,
                            'none'
                        ));
                }
                return $finalResponse;
            } else {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'Missing or invalid parameters',
                    'missing_parameters' => $err
                ], 400);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     *
     * Récupérer les données d'une prédiction
     */
    #[Route('/prediction/get', name: 'twitch_prediction_get', methods: ['POST'])]
    public function getPrediction(Request $request): JsonResponse
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            if (count($err) == 0) {
                $accessToken = $this->twitchApiService->validateToken($request, $data['access_token'], $data['refresh_token'], $channelId);
                $response = $this->twitchApiService->getPrediction($accessToken, $channelId);
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'response' => $response
                    ],
                    200,
                );
                if ($channelId == $this->translateJwt($request)['twitchId']) {
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $this->getJwt($request),
                            new \DateTime('+1 day'),
                            '/',
                            'localhost',
                            true,
                            true,
                            false,
                            'none'
                        ));
                }
                return $finalResponse;
            } else {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'Missing or invalid parameters',
                    'missing_parameters' => $err
                ], 400);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     *
     * Mettre fin à une prédiction
     */
    #[Route('/prediction/end', name: 'twitch_prediction_end', methods: ['POST'])]
    public function endPrediction(Request $request): JsonResponse
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            $id = $data['id'] ?? array_push($err, 'id');
            $status = $data['status'] ?? array_push($err, 'status');
            $winningOutcomeId = $data['winningOutcomeId'] ?? null;
            if (count($err) == 0) {
                $accessToken = $this->twitchApiService->validateToken($request, $data['access_token'], $data['refresh_token'], $channelId);
                $response = $this->twitchApiService->endPrediction($accessToken, $channelId, $id, $status, $winningOutcomeId);
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'response' => $response
                    ],
                    200,
                );
                if ($channelId == $this->translateJwt($request)['twitchId']) {
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $this->getJwt($request),
                            new \DateTime('+1 day'),
                            '/',
                            'localhost',
                            true,
                            true,
                            false,
                            'none'
                        ));
                }
                return $finalResponse;
            } else {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'Missing or invalid parameters',
                    'missing_parameters' => $err
                ], 400);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     *
     * Récupérer toutes les prédictions
     */
    #[Route('/prediction/all', name: 'twitch_prediction_all', methods: ['POST'])]
    public function getAllPrediction(Request $request): JsonResponse
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            if (count($err) == 0) {
                $accessToken = $this->twitchApiService->validateToken($request, $data['access_token'], $data['refresh_token'], $channelId);
                $response = $this->twitchApiService->getAllPrediction($accessToken, $channelId);
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'response' => $response
                    ],
                    200,
                );
                if ($channelId == $this->translateJwt($request)['twitchId']) {
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $this->getJwt($request),
                            new \DateTime('+1 day'),
                            '/',
                            'localhost',
                            true,
                            true,
                            false,
                            'none'
                        ));
                }
                return $finalResponse;
            } else {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'Missing or invalid parameters',
                    'missing_parameters' => $err
                ], 400);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     *
     * Create EventSub Subscription
     */
    #[Route('/eventsub/create', name: 'twitch_eventsub_create', methods: ['POST'])]
    public function createEventSub(Request $request): JsonResponse
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $sessionId = $data['session_id'] ?? array_push($err, 'session_id');
            if($data['type'] === "poll" && $data['broadcaster_user_id']) {
                $type = [
                    'channel.poll.begin' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $data['broadcaster_user_id']]],
                    'channel.poll.progress' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $data['broadcaster_user_id']]],
                    'channel.poll.end' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $data['broadcaster_user_id']]],
                ];
            } else if($data['type'] === "prediction" && $data['broadcaster_user_id']) {
                $type = [
                    'channel.prediction.begin' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $data['broadcaster_user_id']]],
                    'channel.prediction.progress' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $data['broadcaster_user_id']]],
                    'channel.prediction.end' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $data['broadcaster_user_id']]],
                ];
            } else {
                array_push($err, 'type');
            }
            $transport = $data['transport'] ?? array_push($err, 'transport');
            if (count($err) == 0) {
                $accessToken = $this->twitchApiService->validateToken($request, $data['access_token'], $data['refresh_token'], $data['broadcaster_user_id']);
                $userTwitch = $this->twitchApiService->fetchUser($accessToken);
                if ($userTwitch['data'][0]['id'] === $data['broadcaster_user_id']) {
                    $accessToken = $this->twitchApiService->validateToken($request, $data['access_token'], $data['refresh_token'], $data['channel_id']);
                    $response = $this->twitchApiService->createEventSubSubscription($accessToken, $sessionId, $type, $transport);
                    $finalResponse = new JsonResponse(
                        [
                            'statusCode' => 200,
                            'response' => $response
                        ],
                        200,
                    );
                    if ($data['broadcaster_user_id'] == $this->translateJwt($request)['twitchId']) {
                        $finalResponse->headers->setCookie(
                            new Cookie(
                                't_access_token_sso',
                                $this->getJwt($request),
                                new \DateTime('+1 day'),
                                '/',
                                'localhost',
                                true,
                                true,
                                false,
                                'none'
                            ));
                    }
                    return $finalResponse;
                } else {
                    return new JsonResponse([
                        'statusCode' => 400,
                        'message' => 'User id is not the same as the condition user id'
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'Missing or invalid parameters',
                    'missing_parameters' => $err
                ], 400);
            }
        } else {
            return $isOk;
        }
    }
}