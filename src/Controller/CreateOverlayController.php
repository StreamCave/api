<?php

namespace App\Controller;

use App\Entity\CameraGroup;
use App\Entity\InfoGroup;
use App\Entity\MatchGroup;
use App\Entity\Model;
use App\Entity\Overlay;
use App\Entity\PollGroup;
use App\Entity\PopupGroup;
use App\Entity\TweetGroup;
use App\Entity\Widget;
use App\Repository\LibWidgetRepository;
use App\Repository\ModelRepository;
use App\Repository\OverlayRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateOverlayController extends AbstractController
{
    public function __construct(OverlayRepository $overlayRepository, ModelRepository $modelRepository, LibWidgetRepository $libWidgetRepository, UserRepository $userRepository, ManagerRegistry $doctrine)
    {
        $this->overlayRepository = $overlayRepository;
        $this->modelRepository = $modelRepository;
        $this->libWidgetRepository = $libWidgetRepository;
        $this->userRepository = $userRepository;
        $this->doctrine = $doctrine;
    }

    public function __invoke(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        $data = json_decode($request->getContent(), true);
        $overlay = new Overlay();
        $overlay->setName($data["name"]);
        $userOwner = $this->userRepository->findOneBy(['id' => explode('/', $data["userOwner"])[3]]);
        $overlay->setUserOwner($userOwner);
        foreach ($data["userAccess"] as $item) {
            $userAccess = $this->userRepository->findOneBy(['id' => explode('/', $item)[3]]);
            $overlay->addUserAccess($userAccess);
        }

        if ($data["Model"]["uuid"] != null) {
            $model = $this->modelRepository->findOneBy(['uuid' => $data["Model"]["uuid"]]);
            $overlay->setModel($model);
        } else {
            // Créer un model si l'uuidModel est null
            $model = new Model();
            $model->setName($data["Model"]["name"]);
            $model->setImage($data["Model"]["image"]);
            $model->setDescription($data["Model"]["description"]);
            $model->setPrice($data["Model"]["price"]);
            $model->addOverlay($overlay);
            $em->persist($model);
            $em->flush();
        }

        // On setup les groups
        $infoGroup = new InfoGroup();
        $cameraGroup = new CameraGroup();
        $matchGroup = new MatchGroup();
        $pollGroup = new PollGroup();
        $popupGroup = new PopupGroup();
        $tweetGroup = new TweetGroup();

        // On fait le tour du array $data["widgets"] pour créer les widgets un par un
        foreach ($data["widgets"] as $widget) {
            $newWidget = new Widget();
            $newWidget->setName($widget["name"]);
            $newWidget->setDescription($widget["description"]);
            $newWidget->setImage($widget["image"]);
            $newWidget->setVisible(false);

            $libWidget = $this->libWidgetRepository->findOneBy(['nameWidget' => $widget["name"]]);

            if ($libWidget != null) {
                match ($libWidget->getNameGroup()) {
                    'info' => $newWidget->setInfoGroup($infoGroup),
                    'camera' => $newWidget->addCameraGroup($cameraGroup),
                    'match' => $newWidget->addMatchGroup($matchGroup),
                    'poll' => $newWidget->setPollGroup($pollGroup),
                    'popup' => $newWidget->setPopupGroup($popupGroup),
                    'tweet' => $newWidget->setTweetGroup($tweetGroup),
                };
            }

            $em->persist($newWidget);
            $em->flush();
        }

        $em->persist($overlay);
        $em->flush();

        return $this->json([
            "statusCode" => 200,
            "data" => $overlay
        ]);
    }
}
