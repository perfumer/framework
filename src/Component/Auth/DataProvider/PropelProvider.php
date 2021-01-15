<?php

namespace Perfumer\Component\Auth\DataProvider;

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
    public function getData(string $token)
    {
        $hashed_token = hash('sha512', $token);

        $session_entry = SessionEntryQuery::create()->findOneByToken($hashed_token);

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
     * @param string $data
     * @return array
     */
    public function getTokens(string $data): array
    {
        return SessionEntryQuery::create()
            ->select('Token')
            ->filterByModelId($data)
            ->find()
            ->getData();
    }

    /**
     * @param string $token
     * @param string $data
     * @return bool
     */
    public function saveData(string $token, string $data): bool
    {
        $hashed_token = hash('sha512', $token);

        $session_entry = new SessionEntry();
        $session_entry->setToken($hashed_token);
        $session_entry->setModelId($data);
        $session_entry->setModelName('App\\Model\\User');

        $expired_at = (new \DateTime())->modify('+' . $this->lifetime . ' second');

        $session_entry->setExpiredAt($expired_at);

        return (bool) $session_entry->save();
    }

    /**
     * @param string $token
     * @return bool
     */
    public function deleteToken(string $token): bool
    {
        $hashed_token = hash('sha512', $token);

        SessionEntryQuery::create()
            ->filterByToken($hashed_token)
            ->delete();

        return true;
    }
}
