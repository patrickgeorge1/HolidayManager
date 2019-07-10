<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class RequestFreeDaysVoter extends Voter
{

    private $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['ENDYEAR'])
            && $subject instanceof User;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $subject */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        $date = new \DateTime();
        switch ($attribute) {
            case 'ENDYEAR':
                // this is author

                if ($subject->getFreeDays() > 0 && intval($date->format('m')) == 12 && intval($date->format('d')) >= 15 && $subject == $user) {
                    return true;
                }

                // for testing
//                if ($subject->getFreeDays() > 0 && intval($date->format('m')) == 7 && intval($date->format('d')) >= 1 && $subject == $user) {
//                    return true;
//                }
                return false;

        }

        return false;
    }
}
