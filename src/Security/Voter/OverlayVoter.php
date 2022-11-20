<?php

namespace App\Security\Voter;

use App\Entity\Overlay;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class OverlayVoter extends Voter
{
    public const OVERLAY_EDIT = 'OVERLAY_EDIT';
    public const OVERLAY_VIEW = 'OVERLAY_VIEW';

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::OVERLAY_EDIT, self::OVERLAY_VIEW])
            && $subject instanceof \App\Entity\Overlay;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $subject = $subject instanceof Overlay ? $subject : null;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::OVERLAY_EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                if ($subject->getUserOwner() === $user) {
                    return true;
                }
                if ($subject->getUserAccess()->contains($user)) {
                    return true;
                }
                break;
            case self::OVERLAY_VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                if ($subject->getUserOwner() === $user) {
                    return true;
                }
                if ($subject->getUserAccess()->contains($user)) {
                    return true;
                }
        }

        return false;
    }
}
