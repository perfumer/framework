<?php

namespace Perfumer\Component\Auth\Authorization;

use Perfumer\Component\Auth\Authentication;
use Perfumer\Component\Auth\Exception\AuthException;

class DatabaseAuthorization extends Authentication
{
    /**
     * @param string $username
     * @param string $password
     * @param bool|false $force_login
     */
    public function login($username, $password, $force_login = false)
    {
        try {
            $query = $this->options['model'] . 'Query';

            $user = $query::create()->findOneBy($this->options['username_field'], $username);

            if (!$user) {
                throw new AuthException(self::STATUS_INVALID_USERNAME);
            }

            if (!$force_login) {
                if (!$user->validatePassword($password)) {
                    throw new AuthException(self::STATUS_INVALID_PASSWORD);
                }

                if ($user->isDisabled()) {
                    throw new AuthException(self::STATUS_ACCOUNT_DISABLED);
                }

                if ($user->getBannedTill() !== null && $user->getBannedTill()->diff(new \DateTime())->invert == 1) {
                    throw new AuthException(self::STATUS_ACCOUNT_BANNED);
                }
            }

            $this->user = $user;
            $this->user->setLogged(true);

            if ($this->options['acl']) {
                $this->user->revealRoles();
            }

            $this->status = self::STATUS_SIGNED_IN;

            $this->startUserSession();

            $this->is_logged = true;
        } catch (AuthException $e) {
            $this->reset($e->getMessage());
        }
    }
}
