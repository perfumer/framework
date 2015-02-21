<?php

namespace Perfumer\Component\Auth\Authorization;

use App\Model\Token;
use App\Model\TokenQuery;
use App\Model\User;
use App\Model\UserQuery;
use Perfumer\Component\Auth\Authentication;
use Perfumer\Component\Auth\Exception\AuthException;

class DatabaseAuthorization extends Authentication
{
    public function login($username, $password, $force_login = false)
    {
        try
        {
            $user = UserQuery::create()->findOneByUsername($username);

            if (!$user)
                throw new AuthException(self::STATUS_INVALID_USERNAME);

            if (!$force_login)
            {
                if (!$user->validatePassword($password))
                    throw new AuthException(self::STATUS_INVALID_PASSWORD);

                if ($user->isDisabled())
                    throw new AuthException(self::STATUS_ACCOUNT_DISABLED);

                if ($user->getBannedTill() !== null && $user->getBannedTill()->diff(new \DateTime())->invert == 1)
                    throw new AuthException(self::STATUS_ACCOUNT_BANNED);
            }

            $this->user = $user;
            $this->user->setLogged(true);

            $this->startSession();

            $this->status = self::STATUS_SIGNED_IN;

            $this->token_handler->setToken($this->token);
        }
        catch (AuthException $e)
        {
            $this->reset($e->getMessage());
        }
    }
}