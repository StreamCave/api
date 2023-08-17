<?php

namespace App\Controller;

use App\Repository\StatGroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetStatGroupByOverlay extends AbstractController
{
    public function __construct(StatGroupRepository $StatGroupRepository, ManagerRegistry $doctrine)
    {
        $this->StatGroupRepository = $StatGroupRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke(Request $request, $overlayId): Response
    {
        $sql = "
            SELECT DISTINCT
                sg.match_id , 
                sg.overlay_id , 
                sg.status , 
                sg.score , 
                rsp.id AS player_id,
                rsp.round, 
                rsp.pseudo, 
                rsp.kills , 
                rsp.deaths , 
                rsp.hp, 
                rsp.score AS player_score, 
                rsp.operator , 
                rsp.team 
            FROM stat_group sg 
            JOIN r6_stats_players rsp ON sg.match_id = rsp.match_id 
            WHERE overlay_id  = ?
        ";
        
        $stmt = $this->doctrine->getManager()->getConnection();
        $results = $stmt->fetchAllAssociative($sql, [$overlayId]);
        if(sizeof($results) > 0) {
            $transformedArray = [];
            foreach ($results as $item) {
                $matchId = $item["match_id"];
                if (!isset($transformedArray[$matchId])) {
                    $transformedArray[$matchId] = [
                        "infos" => [
                            "status" => $item["status"],
                            "score_blue" => json_decode($item["score"], true)["blue"],
                            "score_orange" => json_decode($item["score"], true)["orange"],
                        ],
                        "rounds" => [],
                    ];
                }
                $roundNumber = "round" . $item["round"];
                if (!isset($transformedArray[$matchId]["rounds"][$roundNumber])) {
                    $transformedArray[$matchId]["rounds"][$roundNumber] = [];
                }
                $teamKey = "team_" . $item["team"];
                $transformedArray[$matchId]["rounds"][$roundNumber][$teamKey][] = [
                    "id" => $item["player_id"],
                    "username" => $item["pseudo"],
                    "kill" => $item["kills"],
                    "deaths" => $item["deaths"],
                    "score" => $item["player_score"],
                    "operator_id" => $item["operator"],
                ];
            }
                return $this->json([
                    "statusCode" => 200,
                    "data" => $transformedArray
                ]);
        } else {
            return $this->json([
                "statusCode" => 200,
                "data" => "No data found." 
            ]);
        }
    }
}
