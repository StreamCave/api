<?php

namespace App\Controller;

use App\Service\TwitchApiService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/middleware/twitch', methods: ['POST'])]
class TwitchMiddlewareApi extends AbstractController {

    public function __construct(TwitchApiService $twitchApiService)
    {
        $this->twitchApiService = $twitchApiService;
    }

    private function decodeData(Request $request) {
        return json_decode($request->getContent(), true);
    }

    #[Route('/user', name: 'twitch_user', methods: ['POST'])]
    public function getUserInfo(Request $request): Response
    {
        $data = $this->decodeData($request);
        $accessToken = $data['access_token'];
        $userTwitch = $this->twitchApiService->fetchUser($accessToken);
        return $this->json([
            'statusCode' => 200,
            'user' => $userTwitch
        ]);
    }

    #[Route('/channel', name: 'twitch_channel', methods: ['POST'])]
    public function getChannel(Request $request): Response
    {
        $data = $this->decodeData($request);
        $channelId = $data['channel_id'];
        $accessToken = $data['access_token'];
        $userTwitch = $this->twitchApiService->fetchUser($accessToken);
        if ($userTwitch['data'][0]['id'] != $channelId) {
            return $this->json([
                'statusCode' => 400,
                'message' => 'Channel ID does not match with the access token'
            ]);
        }
        $channel = $this->twitchApiService->fetchChannel($accessToken, $channelId);
        return $this->json([
            'statusCode' => 200,
            'channelId' => $channel
        ]);
    }

    #[Route('/moderators', name: 'twitch_moderators', methods: ['POST'])]
    public function getModerators(Request $request): Response
    {
        $data = $this->decodeData($request);
        $channelId = $data['channel_id'];
        $accessToken = $data['access_token'];
        $userTwitch = $this->twitchApiService->fetchUser($accessToken);
        if ($userTwitch['data'][0]['id'] != $channelId) {
            return $this->json([
                'statusCode' => 400,
                'message' => 'Channel ID does not match with the access token'
            ]);
        }
        $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
        return $this->json([
            'statusCode' => 200,
            'moderators' => $moderators
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * Check si l'user est bien modérateur du channel
     */
    #[Route('/check-access', name: 'twitch_check_access', methods: ['POST'])]
    public function checkAccessChannel(Request $request): Response
    {
        $data = $this->decodeData($request);
        $channelId = $data['channel_id'];
        $accessToken = $data['access_token'];

        $userTwitch = $this->twitchApiService->fetchUser($accessToken);
        if ($userTwitch['data'][0]['id'] != $channelId) {
            return $this->json([
                'statusCode' => 400,
                'message' => 'Channel ID does not match with the access token'
            ]);
        }

        $moderators = $this->twitchApiService->fetchModerators($accessToken, $channelId);
        $isModerator = false;

        if ($channelId !== $userTwitch['data'][0]['id']) {
            foreach ($moderators as $moderator) {
                if ($moderator['user_id'] == $userTwitch['data'][0]['id']) {
                    $isModerator = true;
                }
            }
        } else {
            $isModerator = true;
        }

        return $this->json([
            'statusCode' => 200,
            'isModerator' => $isModerator
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * Créer un poll
     */
    #[Route('/poll/create', name: 'twitch_poll_create', methods: ['POST'])]
    public function createPoll(Request $request): Response
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk->getStatusCode() === 200) {
            $data = $this->decodeData($request);
            $err = [];
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
                return $this->json([
                    'statusCode' => 200,
                    'response' => $response
                ]);
            } else {
                return $this->json([
                    'statusCode' => 400,
                    'message' => 'Missing parameters',
                    'missing_parameters' => $err
                ]);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * Récupérer les données du poll
     */
    #[Route('/poll/get', name: 'twitch_poll_get', methods: ['POST'])]
    public function getPoll(Request $request) : Response
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk->getStatusCode() === 200) {
            $data = $this->decodeData($request);
            $err = [];
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            $id = $data['id'] ?? array_push($err, 'id');
            if (count($err) == 0) {
                $response = $this->twitchApiService->getPoll($accessToken, $channelId, $id);
                return $this->json([
                    'statusCode' => 200,
                    'response' => $response
                ]);
            } else {
                return $this->json([
                    'statusCode' => 400,
                    'message' => 'Missing parameters',
                    'missing_parameters' => $err
                ]);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * Mettre fin à un poll en cours
     */
    #[Route('/poll/end', name: 'twitch_poll_end', methods: ['POST'])]
    public function endPoll(Request $request): Response
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            $id = $data['id'] ?? array_push($err, 'id');
            $status = $data['status'] ?? null;
            if (count($err) == 0) {
                $response = $this->twitchApiService->endPoll($accessToken, $channelId, $id, $status);
                return $this->json([
                    'statusCode' => 200,
                    'response' => $response
                ]);
            } else {
                return $this->json([
                    'statusCode' => 400,
                    'message' => 'Missing parameters',
                    'missing_parameters' => $err
                ]);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * Récupérer tous les polls
     */
    #[Route('/poll/all', name: 'twitch_poll_all', methods: ['POST'])]
    public function getAllPoll(Request $request): Response
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            if (count($err) == 0) {
                $response = $this->twitchApiService->getPolls($accessToken, $channelId);
                return $this->json([
                    'statusCode' => 200,
                    'response' => $response
                ]);
            } else {
                return $this->json([
                    'statusCode' => 400,
                    'message' => 'Missing parameters',
                    'missing_parameters' => $err
                ]);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * Créer une prédiction
     */
    #[Route('/prediction/create', name: 'twitch_prediction_create', methods: ['POST'])]
    public function createPrediction(Request $request): Response
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            $title = $data['title'] ?? array_push($err, 'title');
            $outcomes = $data['outcomes'] ?? array_push($err, 'outcomes');
            $predictionWindow = $data['predictionWindow'] ?? array_push($err, 'predictionWindow');
            if (count($err) == 0) {
                $response = $this->twitchApiService->createPrediction($accessToken, $channelId, $title, $outcomes, $predictionWindow);
                return $this->json([
                    'statusCode' => 200,
                    'response' => $response
                ]);
            } else {
                return $this->json([
                    'statusCode' => 400,
                    'message' => 'Missing parameters',
                    'missing_parameters' => $err
                ]);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * Récupérer les données d'une prédiction
     */
    #[Route('/prediction/get', name: 'twitch_prediction_get', methods: ['POST'])]
    public function getPrediction(Request $request): Response
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            $id = $data['id'] ?? array_push($err, 'id');
            if (count($err) == 0) {
                $response = $this->twitchApiService->getPrediction($accessToken, $channelId, $id);
                return $this->json([
                    'statusCode' => 200,
                    'response' => $response
                ]);
            } else {
                return $this->json([
                    'statusCode' => 400,
                    'message' => 'Missing parameters',
                    'missing_parameters' => $err
                ]);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * Mettre fin à une prédiction
     */
    #[Route('/prediction/end', name: 'twitch_prediction_end', methods: ['POST'])]
    public function endPrediction(Request $request): Response
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            $id = $data['id'] ?? array_push($err, 'id');
            $status = $data['status'] ?? array_push($err, 'status');
            $winningOutcomeId = $data['winningOutcomeId'] ?? null;
            if (count($err) == 0) {
                $response = $this->twitchApiService->endPrediction($accessToken, $channelId, $id, $status, $winningOutcomeId);
                return $this->json([
                    'statusCode' => 200,
                    'response' => $response
                ]);
            } else {
                return $this->json([
                    'statusCode' => 400,
                    'message' => 'Missing parameters',
                    'missing_parameters' => $err
                ]);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * Récupérer toutes les prédictions
     */
    #[Route('/prediction/all', name: 'twitch_prediction_all', methods: ['POST'])]
    public function getAllPrediction(Request $request): Response
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
            $accessToken = $data['access_token'] ?? array_push($err, 'access_token');
            $channelId = $data['channel_id'] ?? array_push($err, 'channel_id');
            if (count($err) == 0) {
                $response = $this->twitchApiService->getAllPrediction($accessToken, $channelId);
                return $this->json([
                    'statusCode' => 200,
                    'response' => $response
                ]);
            } else {
                return $this->json([
                    'statusCode' => 400,
                    'message' => 'Missing parameters',
                    'missing_parameters' => $err
                ]);
            }
        } else {
            return $isOk;
        }
    }

    /**
     * @param Request $request
     * @return Response
     *
     * Create EventSub Subscription
     */
    #[Route('/eventsub/create', name: 'twitch_eventsub_create', methods: ['POST'])]
    public function createEventSub(Request $request): Response
    {
        $isOk = $this->checkAccessChannel($request);
        if ($isOk) {
            $data = $this->decodeData($request);
            $err = [];
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
                $userTwitch = $this->twitchApiService->fetchUser($accessToken);
                if ($userTwitch['data'][0]['id'] === $data['broadcaster_user_id']) {
                    $response = $this->twitchApiService->createEventSubSubscription($accessToken, $sessionId, $type, $transport);
                    return $this->json([
                        'statusCode' => 200,
                        'response' => $response
                    ]);
                } else {
                    return $this->json([
                        'statusCode' => 400,
                        'message' => 'User id is not the same as the condition user id'
                    ]);
                }
            } else {
                return $this->json([
                    'statusCode' => 400,
                    'message' => 'Missing parameters',
                    'missing_parameters' => $err
                ]);
            }
        } else {
            return $isOk;
        }
    }
}