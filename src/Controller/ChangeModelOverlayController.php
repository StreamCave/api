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
use App\Repository\LibWidgetRepository;
use App\Repository\ModelRepository;
use App\Repository\OverlayRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChangeModelOverlayController extends AbstractController {

    public function __construct(ManagerRegistry $doctrine, OverlayRepository $overlayRepository, UserRepository $userRepository, ModelRepository $modelRepository, LibWidgetRepository $libWidgetRepository)
    {
        $this->doctrine = $doctrine;
        $this->overlayRepository = $overlayRepository;
        $this->userRepository = $userRepository;
        $this->modelRepository = $modelRepository;
        $this->libWidgetRepository = $libWidgetRepository;
    }

    public function __invoke(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $em = $this->doctrine->getManager();
        $uuidNewModel = explode('/', $data['Model'])[3];
        $overlayDb = $this->overlayRepository->findOneBy(['uuid' => $uuidNewModel]);
        $overlayDb->setName($data['name'] ?? $overlayDb->getName());
        $overlayDb->setImage($data['image'] ?? $overlayDb->getImage());
        // Update Model en supprimant les anciens widgets et en ajoutant les nouveaux
        foreach ($overlayDb->getWidgets() as $widget) {
            $overlayDb->removeWidget($widget);
        }
        // On regarde dans les rules du model la liste des widgets à ajouter si différent de null
        // Sortir le uuid du model /api/models/flowup
        $newModel = $this->modelRepository->findOneBy(['uuid' => $uuidNewModel]);
        $widgetsInRules = $newModel->getRules()['Widgets'];
        $infoGroup = new InfoGroup();
        $cameraGroup = new CameraGroup();
        $matchGroup = new MatchGroup();
        $pollGroup = new PollGroup();
        $popupGroup = new PopupGroup();
        $tweetGroup = new TweetGroup();
        $twitchGroup = new TwitchGroup();
        foreach ($widgetsInRules as $widget) {
            $newWidget = new Widget();
            $newWidget->setName(ucfirst($widget));
            $newWidget->setDescription("Widget : " . ucfirst($widget));
            $newWidget->setImage(null);
            $newWidget->setVisible(false);

            $libWidget = $this->libWidgetRepository->findOneBy(['nameWidget' => $widget]);

            if ($libWidget != null) {
                match ($libWidget->getNameGroup()) {
                    'info' => $newWidget->setInfoGroup($infoGroup),
                    'camera' => $newWidget->addCameraGroup($cameraGroup),
                    'match' => $newWidget->addMatchGroup($matchGroup),
                    'poll' => $newWidget->setPollGroup($pollGroup),
                    'popup' => $newWidget->setPopupGroup($popupGroup),
                    'tweet' => $newWidget->setTweetGroup($tweetGroup),
                    'twitch' => $newWidget->setTwitchGroup($twitchGroup)
                };
            }

            $em->persist($newWidget);
            $em->flush();
        }

        $overlayDb->setModel($newModel);

        $em->persist($overlayDb);
        $em->flush();

        return $this->json([
            'message' => 'Overlay updated !',
            'overlay' => $overlayDb
        ]);
    }
}