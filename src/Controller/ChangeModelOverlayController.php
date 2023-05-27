<?php

namespace App\Controller;

use App\Entity\CameraGroup;
use App\Entity\InfoGroup;
use App\Entity\MatchGroup;
use App\Entity\PollGroup;
use App\Entity\PopupGroup;
use App\Entity\TweetGroup;
use App\Entity\TwitchGroup;
use App\Entity\Widget;
use App\Repository\InfoGroupRepository;
use App\Repository\LibWidgetRepository;
use App\Repository\ModelRepository;
use App\Repository\OverlayRepository;
use App\Repository\UserRepository;
use App\Repository\WidgetRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Uuid;

class ChangeModelOverlayController extends AbstractController {

    public function __construct(
        ManagerRegistry $doctrine,
        OverlayRepository $overlayRepository,
        UserRepository $userRepository,
        ModelRepository $modelRepository,
        LibWidgetRepository $libWidgetRepository,
        WidgetRepository $widgetRepository,
        InfoGroupRepository $infoGroupRepository
    )
    {
        $this->doctrine = $doctrine;
        $this->overlayRepository = $overlayRepository;
        $this->userRepository = $userRepository;
        $this->modelRepository = $modelRepository;
        $this->libWidgetRepository = $libWidgetRepository;
        $this->widgetRepository = $widgetRepository;
        $this->infoGroupRepository = $infoGroupRepository;
    }

    private function generateInfoGroup() : InfoGroup
    {
        $em = $this->doctrine->getManager();
        $infoGroup = new InfoGroup();
        $infoGroup->setTitre("Info");
        $infoGroup->setDescription("Groupe d'information");
        $infoGroup->setLogo(null);
        $infoGroup->setTextScroll(['Ceci est un texte défilant', 'Ceci est un autre texte défilant', 'Ceci est un dernier texte défilant']);
        $infoGroup->setTeamNameA('Alpha');
        $infoGroup->setTeamNameB('Bravo');

        $em->persist($infoGroup);
        $em->flush();

        return $infoGroup;
    }

    private function generateCameraGroup($team) : CameraGroup
    {
        $em = $this->doctrine->getManager();
        $cameraGroup = new CameraGroup();
        $cameraGroup->setVisible(false);
        $cameraGroup->setName('Camera');
        $cameraGroup->setSocketId(Uuid::v4());
        $cameraGroup->setTeam($team);
        $cameraGroup->setMetadata('metadata');

        $em->persist($cameraGroup);
        $em->flush();

        return $cameraGroup;
    }

    private function handleCameraGroup($cameraRules, $newWidget) {
        $team = ['Alpha', 'Beta'];
        for ($i = 0; $i < $cameraRules['numberOfGroup']; $i++) {
            for ($j = 0; $j < $cameraRules['maxPerGroup']; $j++) {
                $cameraGroup = $this->generateCameraGroup($team[$i]);
                $newWidget->addCameraGroup($cameraGroup);
            }
        }
    }

    private function generateMatchGroup($overlayId, $newWidget) : MatchGroup
    {
        $em = $this->doctrine->getManager();
        $matchGroup = new MatchGroup();
        $matchGroup->setTeamNameA('Alpha');
        $matchGroup->setTeamNameB('Bravo');
        $matchGroup->setScoreA(0);
        $matchGroup->setScoreB(0);
        $matchGroup->setLogoTeamA(null);
        $matchGroup->setLogoTeamB(null);
        $matchGroup->setPlayersTeamA(['Alpha1', 'Alpha2', 'Alpha3', 'Alpha4', 'Alpha5']);
        $matchGroup->setPlayersTeamB(['Bravo1', 'Bravo2', 'Bravo3', 'Bravo4', 'Bravo5']);
        $matchGroup->setNextMatch(false);
        $matchGroup->setVisible(false);
        $matchGroup->setOverlayId($overlayId);
        $matchGroup->setRounds("BO1");
        $matchGroup->setHours("00:00");
        $matchGroup->setMapName('Map');
        $matchGroup->addWidget($newWidget);

        $em->persist($matchGroup);
        $em->flush();

        return $matchGroup;
    }

    public function __invoke(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $em = $this->doctrine->getManager();

        $uuidNewModel = explode('/', $data['Model'])[3];
        $uuidOverlay = $request->attributes->get('uuid');

        $overlayDb = $this->overlayRepository->findOneBy(['uuid' => $uuidOverlay]);
        $newModel = $this->modelRepository->findOneBy(['uuid' => $uuidNewModel]);

        $overlayDb->setName($data['name'] ?? $overlayDb->getName());
        $overlayDb->setImage($data['image'] ?? $overlayDb->getImage());
        // Update Model en supprimant les anciens widgets et en ajoutant les nouveaux
        foreach ($overlayDb->getWidgets()->getValues() as $widget) {
            $overlayDb->removeWidget($widget);
            $em->remove($widget);
            $em->flush();
        }
        // On regarde dans les rules du model la liste des widgets à ajouter si différent de null
        // Sortir le uuid du model /api/models/flowup
        $widgetsInRules = $newModel->getRules()['Widgets'];
        $cameraRules = $newModel->getRules()['Cameras'];
        // Copier les groups de widgets du model dans l'overlay

//        $infoGroup = new InfoGroup();
//        $cameraGroup = new CameraGroup();
//        $matchGroup = new MatchGroup();
        $pollGroup = new PollGroup();
        $popupGroup = new PopupGroup();
        $tweetGroup = new TweetGroup();
        $twitchGroup = new TwitchGroup();
        foreach ($widgetsInRules as $widget) {
            $newWidget = new Widget();
            $newWidget->setName($widget);
            $newWidget->setDescription("Widget : " . ucfirst($widget));
            $newWidget->setImage(null);
            $newWidget->setVisible(false);

            $libWidget = $this->libWidgetRepository->findOneBy(['nameWidget' => $widget]);

            if ($libWidget != null) {
                match ($libWidget->getNameGroup()) {
                    'info' => $newWidget->setInfoGroup($this->generateInfoGroup()),
                    'camera' => $this->handleCameraGroup($cameraRules, $newWidget),
                    'match' => $newWidget->addMatchGroup($this->generateMatchGroup($overlayDb->getUuid(), $newWidget)),
                    'poll' => $newWidget->setPollGroup($pollGroup),
                    'popup' => $newWidget->setPopupGroup($popupGroup),
                    'tweet' => $newWidget->setTweetGroup($tweetGroup),
                    'twitch' => $newWidget->setTwitchGroup($twitchGroup)
                };
            }


            $em->persist($newWidget);
            $em->flush();
            $overlayDb->addWidget($newWidget);
        }

        $overlayDb->setModel($newModel);

        $em->persist($overlayDb);
        $em->flush();

        return $this->json([
            'statusCode' => 200,
            'message' => 'Overlay updated !',
            'overlay' => $overlayDb
        ]);
    }
}