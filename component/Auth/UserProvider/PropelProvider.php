<?php

namespace Perfumer\Component\Auth\UserProvider;

use App\Model\SessionEntry;
use App\Model\SessionEntryQuery;
use App\Model\UserQuery;

class PropelProvider extends AbstractProvider
{
    /**
     * @var int
     */
    protected $lifetime = 3600;

    /**
     * PropelProvider constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (isset($options['lifetime'])) {
            $this->lifetime = (int) $options['lifetime'];
        }
    }

    /**
     * @param string $token
     * @return mixed
     */
    public function getUserId($token)
    {
        $session_entry = SessionEntryQuery::create()->findOneByToken($token);

        if (!$session_entry) {
            return null;
        }

        if ($session_entry->getExpiredAt() !== null && $session_entry->getExpiredAt()->diff(new \DateTime())->invert == 0) {
            return null;
        }

        $user = UserQuery::create()->findPk($session_entry->getModelId());

        if (!$user) {
            return null;
        }

        $expired_at = (new \DateTime())->modify('+' . $this->lifetime . ' second');

        $session_entry->setExpiredAt($expired_at);
        $session_entry->save();

        return $session_entry->getModelId();
    }

    /**
     * @param string $token
     * @param mixed $id
     * @return bool
     */
    public function setUserToken($token, $id)
    {
        $session_entry = new SessionEntry();
        $session_entry->setToken($token);
        $session_entry->setModelId($id);
        $session_entry->setModelName('App\\Model\\User');

        $expired_at = (new \DateTime())->modify('+' . $this->lifetime . ' second');

        $session_entry->setExpiredAt($expired_at);

        return (bool) $session_entry->save();
    }
}
