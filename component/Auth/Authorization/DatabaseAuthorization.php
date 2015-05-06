<?php

namespace Perfumer\Component\Auth\Authorization;

use App\Model\ApplicationQuery;
use App\Model\UserQuery;
use Perfumer\Component\Auth\Authentication;
use Perfumer\Component\Auth\Exception\AuthException;

class DatabaseAuthorization extends Authentication
{
    public function login($username, $password, $application_token = null, $force_login = false)
    {
        try
        {
            if ($this->options['application'])
            {
                $application = ApplicationQuery::create()->findOneByToken($application_token);

                if (!$application)
                    throw new AuthException(self::STATUS_INVALID_APPLICATION);

                $this->application = $application;
            }

            $user = UserQuery::create()->findOneByUsername($username);

            if (!$user)
                throw new AuthException(self::STATUS_INVALID_USERNAME);

            if (!$force_login)
            {
                if (!$user->validatePassword($password))
                    throw new AuthException(self::STATUS_INVALID_PASSWORD);

                if ($this->options['groups'] && !$user->inGroup($this->options['groups']))
                    throw new AuthException(self::STATUS_INVALID_GROUP);

                if ($user->isDisabled())
                    throw new AuthException(self::STATUS_ACCOUNT_DISABLED);

                if ($user->getBannedTill() !== null && $user->getBannedTill()->diff(new \DateTime())->invert == 0)
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