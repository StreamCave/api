<?php

namespace App\Controller;

use App\Repository\TwitchGroupRepository;
use App\Repository\UserRepository;
use App\Repository\WidgetRepository;
use App\Service\TwitchApiService;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/middleware/twitch', methods: ['POST'])]
class TwitchMiddlewareApi extends AbstractController {

    public function __construct(TwitchApiService $twitchApiService, JWTEncoderInterface $jwtEncoder, UserRepository $userRepository, TwitchGroupRepository $twitchGroupRepository, WidgetRepository $widgetRepository, ManagerRegistry $doctrine)
    {
        $this->twitchApiService = $twitchApiService;
        $this->jwtEncoder = $jwtEncoder;
        $this->userRepository = $userRepository;
        $this->twitchGroupRepository = $twitchGroupRepository;
        $this->widgetRepository = $widgetRepository;
        $this->doctrine = $doctrine;
    }

    /**
     * @param Request $request
     * @return mixed
     *
     * Décode les données envoyées par le front
     */
    private function decodeData(Request $request) {
        return json_decode($request->getContent(), true);
    }

    /**
     * @param $channelId
     * @return bool
     *
     * Vérifie si l'utilisateur a le bon status pour appeler l'API Twitch (affilié minimum)
     */
    private function cantCallTwitch($channelId): bool
    {
        $userDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
        if($userDB !== null) {
            if ($userDB->getTwitchStatus() !== null && $userDB->getTwitchStatus() !== '') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * Récupère les informations de la chaine Twitch ciblée
     */
    #[Route('/channel', name: 'twitch_channel', methods: ['POST'])]
    public function getChannel(Request $request): JsonResponse
    {
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        if (count($err) === 0) {
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $refreshToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $channel = $this->twitchApiService->fetchChannel($accessToken, $refreshToken, $channelId);
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'access_renew' => $channel['refresh'] != null,
                    'channel' => $channel['data'],
                ],
                200,
            );
            if ($channel['refresh'] != null) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $channel['refresh'],
                        new \DateTime('+1 day'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
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
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        if (count($err) === 0) {
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $refreshToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'access_renew' => $moderators['refresh'] != null ? true : false,
                    'moderators' => $moderators['data'],
                ],
                200,
            );
            if ($moderators['refresh'] != null) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $moderators['refresh'],
                        new \DateTime('+1 day'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
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
     * Créer un poll
     */
    #[Route('/poll/create', name: 'twitch_poll_create', methods: ['POST'])]
    public function createPoll(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        $title = $data['title'] ?? array_push($err, 'title');
        $choices = $data['choices'] ?? array_push($err, 'choices');
        $duration = $data['duration'] ?? array_push($err, 'duration');
        $channelPointsVotingEnabled = $data['channel_points_voting_enabled'] ?? array_push($err, 'channel_points_voting_enabled');
        $channelPointsVotingEnabled = $channelPointsVotingEnabled === true ? true : false;
        $overlayId = $data['overlay_id'] ?? array_push($err, 'overlay_id');
        $widgetUuid = $data['widget_uuid'] ?? array_push($err, 'widget_uuid');
        $channelPointsPerVote = 1;
        $pollId = null;
        if($channelPointsVotingEnabled === true) {
            $channelPointsPerVote = $data['channel_points_per_vote'] ?? array_push($err, 'channel_points_per_vote');
        }
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $refreshToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->createPoll($accessToken, $refreshToken, $channelId, $choices, $title, $duration, $channelPointsVotingEnabled, $channelPointsPerVote);
            if($response == null) {
                $pollId = null;
            } else {
                $pollId = $response['data']['id'];
            }
            // Vérifie si TwitchGroup en fonction de overlayId existe, on édite le twitchId et le visible
            $widget = $this->widgetRepository->findOneBy(['uuid' => $widgetUuid]);
            if($pollId != null) {
                if ($widget != null) {
                    $widget = $widget;
                    $widget->setVisible(true);
                    $em = $this->doctrine->getManager();
                    $em->persist($widget);
                    $em->flush();
                }
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'access_renew' => $response['refresh'] != null ? true : false,
                        'data' => $response['data'],
                    ],
                    200,
                );
                if ($response['refresh'] != null) {
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $response['refresh'],
                            new \DateTime('+1 day'),
                            '/',
                            $_ENV['COOKIE_DOMAIN'],
                            true,
                            true,
                            false,
                            'none'
                        ));
                }
            }  else {
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'access_renew' => false,
                        'data' => "You can't create a poll right now.",
                    ],
                    200,
                );
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
     * Récupérer les données du poll
     */
    #[Route('/poll/get', name: 'twitch_poll_get', methods: ['POST'])]
    public function getPoll(Request $request) : JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
        $broadcastId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        $overlayId = $data['overlay_id'] ?? array_push($err, 'overlay_id');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            // Vérifier la validité du token
            $accessToken = $this->twitchApiService->validateToken($accessToken, $refreshToken);
            if ($accessToken === null) {
                return new JsonResponse([
                    'statusCode' => 401,
                    'message' => 'Invalid token'
                ], 401);
            }
            // Vérifier si broadcaster_id correspond à channel_id ou si channel_id est modérateur de broadcaster_id
            if ($channelId != $broadcastId) {
                $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId, $broadcastId);
                if (!$moderators) {
                    return new JsonResponse([
                        'statusCode' => 403,
                        'message' => 'You are not a moderator of this channel'
                    ], 403);
                }
            }
            $response = $this->twitchApiService->getPoll($accessToken, $refreshToken, $channelId);
            if (!$response) {
                return new JsonResponse([
                    'statusCode' => 404,
                    'message' => 'No poll found'
                ], 404);
            }
            $twitchGroup = $this->twitchGroupRepository->findBy(['overlayId' => $overlayId]);
            if ($twitchGroup != null) {
                $twitchGroup = $twitchGroup[0];
                $visible = $twitchGroup->isVisible();
                $finalResponse = new JsonResponse(
                    [
                        'statusCode' => 200,
                        'access_renew' => $response['refresh'] != null,
                        'response' => $response['data'],
                        'visible' => $visible,
                        'overlay_id' => $overlayId
                    ],
                    200,
                );
                } else {
                    $finalResponse = new JsonResponse(
                        [
                            'statusCode' => 200,
                            'access_renew' => $response['refresh'] != null,
                            'response' => $response['data'],
                            'overlay_id' => $overlayId
                        ],
                        200,
                    );
                }
                if ($response['refresh'] != null) {
                    $finalResponse->headers->setCookie(
                        new Cookie(
                            't_access_token_sso',
                            $response['refresh']['access_token'],
                            new \DateTime('+1 day'),
                            '/',
                            $_ENV['COOKIE_DOMAIN'],
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
     * Mettre fin à un poll en cours
     */
    #[Route('/poll/end', name: 'twitch_poll_end', methods: ['POST'])]
    public function endPoll(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        $id = $data['id'] ?? array_push($err, 'id');
        $status = $data['status'] ?? 'TERMINATED';
        $overlayId = $data['overlay_id'] ?? array_push($err, 'overlay_id');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $refreshToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->endPoll($accessToken, $refreshToken, $channelId, $id, $status);
            if (!$response) {
                return new JsonResponse([
                    'statusCode' => 404,
                    'message' => 'No poll found'
                ], 404);
            }
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'access_renew' => $response['refresh'] != null ? true : false,
                    'response' => $response['data'],
                ],
                200,
            );
            if ($response['refresh'] != null) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $response['refresh'],
                        new \DateTime('+1 day'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
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
     * Récupérer tous les polls
     */
    #[Route('/poll/all', name: 'twitch_poll_all', methods: ['POST'])]
    public function getAllPoll(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $refreshToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->getPolls($accessToken, $refreshToken, $channelId);
            if (!$response) {
                return new JsonResponse([
                    'statusCode' => 404,
                    'message' => 'No poll found'
                ], 404);
            }
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'access_renew' => $response['refresh'] != null ? true : false,
                    'response' => $response['data'],
                ],
                200,
            );
            if ($response['refresh'] != null) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $response['refresh'],
                        new \DateTime('+1 day'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
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
     * Créer une prédiction
     */
    #[Route('/prediction/create', name: 'twitch_prediction_create', methods: ['POST'])]
    public function createPrediction(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        $title = $data['title'] ?? array_push($err, 'title');
        $outcomes = $data['outcomes'] ?? array_push($err, 'outcomes');
        $predictionWindow = $data['predictionWindow'] ?? array_push($err, 'predictionWindow');
        $overlayId = $data['overlay_id'] ?? array_push($err, 'overlay_id');
        $widgetUuid = $data['widget_uuid'] ?? array_push($err, 'widget_uuid');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $refreshToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->createPrediction($accessToken, $refreshToken, $channelId, $title, $outcomes, $predictionWindow);
            $predictionId = $response['data'][0]['id'];
            // Vérifie si TwitchGroup en fonction de overlayId existe, on édite le twitchId et le visible
            $widget = $this->widgetRepository->findOneBy(['uuid' => $widgetUuid]);
            if ($widget != null) {
                $widget = $widget;
                $widget->setVisible(true);
                $em = $this->doctrine->getManager();
                $em->persist($widget);
                $em->flush();
            }
            if (!$response) {
                return new JsonResponse([
                    'statusCode' => 404,
                    'message' => 'No poll found'
                ], 404);
            }
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'access_renew' => $response['refresh'] != null ? true : false,
                    'response' => $response['data'],
                ],
                200,
            );
            if ($response['refresh'] != null) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $response['refresh'],
                        new \DateTime('+1 day'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
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
     * Récupérer les données d'une prédiction
     */
    #[Route('/prediction/get', name: 'twitch_prediction_get', methods: ['POST'])]
    public function getPrediction(Request $request): JsonResponse
    {   
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        $overlayId = $data['overlay_id'] ?? array_push($err, 'overlay_id');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $refreshToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->getPrediction($accessToken, $refreshToken, $channelId);
            if (!$response) {
                return new JsonResponse([
                    'statusCode' => 404,
                    'message' => 'No poll found'
                ], 404);
            }
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'access_renew' => $response['refresh'] != null ? true : false,
                    'response' => $response['data'],
                    'overlay_id' => $overlayId
                ],
                200,
            );
            if ($response['refresh'] != null) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $response['refresh'],
                        new \DateTime('+1 day'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
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
     * Mettre fin à une prédiction
     */
    #[Route('/prediction/end', name: 'twitch_prediction_end', methods: ['POST'])]
    public function endPrediction(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        $id = $data['id'] ?? array_push($err, 'id');
        $status = $data['status'] ?? array_push($err, 'status');
        $winningOutcomeId = $data['winningOutcomeId'] ?? null;
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $refreshToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->endPrediction($accessToken, $refreshToken, $channelId, $id, $status, $winningOutcomeId);
            if (!$response) {
                return new JsonResponse([
                    'statusCode' => 404,
                    'message' => 'No poll found'
                ], 404);
            }
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'access_renew' => $response['refresh'] != null ? true : false,
                    'response' => $response['data'],
                ],
                200,
            );
            if ($response['refresh'] != null) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $response['refresh'],
                        new \DateTime('+1 day'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
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
     * Récupérer toutes les prédictions
     */
    #[Route('/prediction/all', name: 'twitch_prediction_all', methods: ['POST'])]
    public function getAllPrediction(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $refreshToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->getAllPrediction($accessToken, $refreshToken, $channelId);
            if (!$response) {
                return new JsonResponse([
                    'statusCode' => 404,
                    'message' => 'No poll found'
                ], 404);
            }
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'access_renew' => $response['refresh'] != null ? true : false,
                    'response' => $response['data'],
                ],
                200,
            );
            if ($response['refresh'] != null) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $response['refresh'],
                        new \DateTime('+1 day'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
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
     * Create EventSub Subscription
     */
    #[Route('/eventsub/create', name: 'twitch_eventsub_create', methods: ['POST'])]
    public function createEventSub(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $request->cookies->get('t_access_token_sso') ?? array_push($err, 't_access_token_sso');
        $refreshToken = $request->cookies->get('t_refresh_token_sso') ?? array_push($err, 't_refresh_token_sso');
        $channelId = $request->cookies->get('broadcaster_id') ?? array_push($err, 'broadcaster_id');
        $sessionId = $data['session_id'] ?? array_push($err, 'session_id');
        if($data['type'] === "poll" && $channelId) {
            $type = [
                'channel.poll.begin' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $channelId]],
                'channel.poll.progress' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $channelId]],
                'channel.poll.end' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $channelId]],
            ];
        } else if($data['type'] === "prediction" && $channelId) {
            $type = [
                'channel.prediction.begin' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $channelId]],
                'channel.prediction.progress' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $channelId]],
                'channel.prediction.end' => ['version' => 1, 'condition' => ['broadcaster_user_id' => $channelId]],
            ];
        } else {
            array_push($err, 'type');
        }
        $transport = $data['transport'] ?? array_push($err, 'transport');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $response = $this->twitchApiService->createEventSubSubscription($accessToken, $refreshToken, $sessionId, $channelId, $type, $transport);
            if (!$response) {
                return new JsonResponse([
                    'statusCode' => 404,
                    'message' => 'No poll found'
                ], 404);
            }
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'access_renew' => $response['refresh'] != null ? true : false,
                    'response' => $response['listener_created'],
                ],
                200,
            );
            if ($response != null && $response['refresh'] != null) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $response['refresh']['access_token'],
                        new \DateTime('+1 day'),
                        '/',
                        $_ENV['COOKIE_DOMAIN'],
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
}