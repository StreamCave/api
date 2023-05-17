<?php

namespace App\Controller;

use App\Entity\TwitchGroup;
use App\Repository\TwitchGroupRepository;
use App\Repository\UserRepository;
use App\Service\TwitchApiService;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/middleware/twitch', methods: ['POST'])]
class TwitchMiddlewareApi extends AbstractController {

    public function __construct(TwitchApiService $twitchApiService, JWTEncoderInterface $jwtEncoder, UserRepository $userRepository, TwitchGroupRepository $twitchGroupRepository, ManagerRegistry $doctrine)
    {
        $this->twitchApiService = $twitchApiService;
        $this->jwtEncoder = $jwtEncoder;
        $this->userRepository = $userRepository;
        $this->twitchGroupRepository = $twitchGroupRepository;
        $this->doctrine = $doctrine;
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

    private function cantCallTwitch($channelId): bool
    {
        $userDB = $this->userRepository->findOneBy(['twitchId' => $channelId]);
        if ($userDB->getTwitchStatus() !== null && $userDB->getTwitchStatus() !== '') {
            return true;
        } else {
            return false;
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
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $channel = $this->twitchApiService->fetchChannel($accessToken, $channelId);
            $finalResponse = new JsonResponse(
                [
                    'statusCode' => 200,
                    'access_renew' => $channel['refresh'] != null ? true : false,
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
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
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
     * Créer un poll
     */
    #[Route('/poll/create', name: 'twitch_poll_create', methods: ['POST'])]
    public function createPoll(Request $request): JsonResponse
    {
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
        $overlayId = $data['overlay_id'] ?? array_push($err, 'overlay_id');
        $channelPointsPerVote = 1;
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
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->createPoll($accessToken, $channelId, $choices, $title, $duration, $channelPointsVotingEnabled, $channelPointsPerVote);
            if ($response == null) {
                return new JsonResponse([
                    'statusCode' => 400,
                    'message' => 'Poll already started'
                ], 400);
            }
            $pollId = $response['data'][0]['id'];

            // Vérifie si TwitchGroup en fonction de overlayId existe, on édite le twitchId et le visible
            $twitchGroup = $this->twitchGroupRepository->findBy(['overlayId' => $overlayId]);
            if ($twitchGroup != null) {
                $twitchGroup->setTwitchId($pollId);
                $twitchGroup->setVisible(true);
                $twitchGroup->setType('poll');
                $em = $this->doctrine->getManager();
                $em->persist($twitchGroup);
                $em->flush();
            } else {
                // Créer un twitchGroup
                $twitchGroup = new TwitchGroup();
                $twitchGroup->setTwitchId($pollId);
                $twitchGroup->setVisible(true);
                $twitchGroup->setOverlayId($overlayId);
                $twitchGroup->setType('poll');
                $em = $this->doctrine->getManager();
                $em->persist($twitchGroup);
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
     * Récupérer les données du poll
     */
    #[Route('/poll/get', name: 'twitch_poll_get', methods: ['POST'])]
    public function getPoll(Request $request) : JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->getPoll($accessToken, $channelId);
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
     * Mettre fin à un poll en cours
     */
    #[Route('/poll/end', name: 'twitch_poll_end', methods: ['POST'])]
    public function endPoll(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
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
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->endPoll($accessToken, $channelId, $id, $status);
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
     * Récupérer tous les polls
     */
    #[Route('/poll/all', name: 'twitch_poll_all', methods: ['POST'])]
    public function getAllPoll(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->getPolls($accessToken, $channelId);
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
     * Créer une prédiction
     */
    #[Route('/prediction/create', name: 'twitch_prediction_create', methods: ['POST'])]
    public function createPrediction(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
        $title = $data['title'] ?? array_push($err, 'title');
        $outcomes = $data['outcomes'] ?? array_push($err, 'outcomes');
        $predictionWindow = $data['predictionWindow'] ?? array_push($err, 'predictionWindow');
        $overlayId = $data['overlay_id'] ?? array_push($err, 'overlay_id');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->createPrediction($accessToken, $channelId, $title, $outcomes, $predictionWindow);
            $predictionId = $response['data'][0]['id'];
            // Vérifie si TwitchGroup en fonction de overlayId existe, on édite le twitchId et le visible
            $twitchGroup = $this->twitchGroupRepository->findOneBy(['overlayId', $overlayId]);
            if ($twitchGroup != null) {
                $twitchGroup->setTwitchId($predictionId);
                $twitchGroup->setVisible(true);
                $twitchGroup->setType('prediction');
                $em = $this->doctrine->getManager();
                $em->persist($twitchGroup);
                $em->flush();
            } else {
                // Créer un twitchGroup
                $twitchGroup = new TwitchGroup();
                $twitchGroup->setTwitchId($predictionId);
                $twitchGroup->setVisible(true);
                $twitchGroup->setOverlayId($overlayId);
                $twitchGroup->setType('prediction');
                $em = $this->doctrine->getManager();
                $em->persist($twitchGroup);
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
     * Récupérer les données d'une prédiction
     */
    #[Route('/prediction/get', name: 'twitch_prediction_get', methods: ['POST'])]
    public function getPrediction(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->getPrediction($accessToken, $channelId);
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
     * Mettre fin à une prédiction
     */
    #[Route('/prediction/end', name: 'twitch_prediction_end', methods: ['POST'])]
    public function endPrediction(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
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
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->endPrediction($accessToken, $channelId, $id, $status, $winningOutcomeId);
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
     * Récupérer toutes les prédictions
     */
    #[Route('/prediction/all', name: 'twitch_prediction_all', methods: ['POST'])]
    public function getAllPrediction(Request $request): JsonResponse
    {
        $data = $this->decodeData($request);
        $err = [];
        $jwt = $request->headers->get('Authorization') ?? array_push($err, 'jwt');
        $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
        $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
        if (count($err) == 0) {
            if (!$this->cantCallTwitch($channelId)) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
            if (!$moderators) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'You are not a moderator of this channel'
                ], 403);
            }
            $response = $this->twitchApiService->getAllPrediction($accessToken, $channelId);
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
     * Create EventSub Subscription
     */
    #[Route('/eventsub/create', name: 'twitch_eventsub_create', methods: ['POST'])]
    public function createEventSub(Request $request): JsonResponse
    {
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
            if (!$this->cantCallTwitch($data['broadcaster_user_id'])) {
                return new JsonResponse([
                    'statusCode' => 403,
                    'message' => 'Your channel do not have the rights'
                ], 403);
            }
            $response = $this->twitchApiService->createEventSubSubscription($accessToken, $sessionId, $data['broadcaster_user_id'], $type, $transport);
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
            if ($response['refresh'] != null) {
                $finalResponse->headers->setCookie(
                    new Cookie(
                        't_access_token_sso',
                        $response['refresh'],
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
}