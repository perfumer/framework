<?php

namespace Perfumer\Component\Auth;

use App\Model\Session as SessionEntry;
use App\Model\SessionQuery as SessionEntryQuery;
use Perfumer\Component\Auth\Exception\AuthException;
use Perfumer\Component\Session\TokenHandler\AbstractHandler as TokenHandler;
use Perfumer\Component\Session\Pool as SessionPool;
use Perfumer\Component\Session\Session;
use Perfumer\Helper\Text;
use Propel\Runtime\Map\TableMap;

class Authentication
{
    const STATUS_ACCOUNT_BANNED = 10;
    const STATUS_ACCOUNT_DISABLED = 20;
    const STATUS_ANONYMOUS = 30;
    const STATUS_AUTHENTICATED = 40;
    const STATUS_EXPIRED_TOKEN = 50;
    const STATUS_INVALID_APPLICATION = 60;
    const STATUS_INVALID_CREDENTIALS = 70;
    const STATUS_INVALID_PASSWORD = 80;
    const STATUS_INVALID_TOKEN = 90;
    const STATUS_INVALID_USER = 100;
    const STATUS_INVALID_USERNAME = 110;
    const STATUS_NO_APPLICATION = 120;
    const STATUS_NO_TOKEN = 130;
    const STATUS_REMOTE_SERVER_ERROR = 140;
    const STATUS_SIGNED_IN = 150;
    const STATUS_SIGNED_OUT = 160;

    /**
     * @var SessionPool
     */
    protected $session_pool;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var TokenHandler
     */
    protected $token_handler;

    /**
     * @var bool
     */
    protected $is_logged = false;

    /**
     * @var mixed
     */
    protected $user;

    /**
     * @var SessionEntry
     */
    protected $session_entry;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var array
     */
    protected $options = [];

    public function __construct(SessionPool $session_pool, TokenHandler $token_handler, $options = [])
    {
        $default_options = [
            'model' => '\\App\\Model\\User',
            'username_field' => 'username',
            'acl' => false,
            'update_gap' => 3600
        ];

        $this->options = array_merge($default_options, $options);

        $this->session_pool = $session_pool;
        $this->token_handler = $token_handler;
        $this->token = $this->token_handler->getToken();
    }

    /**
     * @return bool
     */
    public function isLogged()
    {
        $this->init();

        return $this->is_logged;
    }

    /**
     * @return bool
     */
    public function isAnonymous()
    {
        $this->init();

        return $this->status === self::STATUS_ANONYMOUS;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        $this->init();

        if ($this->user === null && $this->is_logged) {
            $this->user = $this->retrieveUser($this->session->getSharedId());

            if ($this->options['acl']) {
                $this->user->revealRoles();
            }
        }

        return $this->user;
    }

    /**
     * @deprecated
     */
    public function getApplication()
    {
        $this->init();

        return null;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        $this->init();

        return $this->status;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        $this->init();

        return $this->session;
    }

    /**
     * @return SessionEntry|null
     */
    public function getSessionEntry()
    {
        $this->init();

        if ($this->session_entry === null && $this->is_logged) {
            $this->session_entry = $this->retrieveSessionEntry($this->token);
        }

        return $this->session_entry;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    public function init()
    {
        if ($this->status === null) {
            $this->authenticate();
        }
    }

    public function authenticate()
    {
        try {
            if ($this->token === null) {
                throw new AuthException(self::STATUS_NO_TOKEN);
            }

            if (!$this->session_pool->has($this->token)) {
                $_session_entry = $this->retrieveSessionEntry($this->token);

                if (!$_session_entry) {
                    throw new AuthException(self::STATUS_INVALID_TOKEN);
                }

                if ($_session_entry->getExpiredAt() !== null && $_session_entry->getExpiredAt()->diff(new \DateTime())->invert == 0) {
                    throw new AuthException(self::STATUS_EXPIRED_TOKEN);
                }

                $user = $this->retrieveUser($_session_entry->getModelId());

                if (!$user) {
                    throw new AuthException(self::STATUS_INVALID_USER);
                }

                if ($user->isDisabled()) {
                    throw new AuthException(self::STATUS_ACCOUNT_DISABLED);
                }

                if ($user->getBannedTill() !== null && $user->getBannedTill()->diff(new \DateTime())->invert == 0) {
                    throw new AuthException(self::STATUS_ACCOUNT_BANNED);
                }

                $this->user = $user;
                $this->user->setLogged(true);

                if ($this->options['acl']) {
                    $this->user->revealRoles();
                }

                $this->is_logged = true;

                $this->session_entry = $_session_entry;

                $this->session = $this->session_pool->get($this->token);
                $this->session->setSharedId($user->getId());

                $this->updateSessionEntry();
            } else {
                $this->session = $this->session_pool->get($this->token);

                $shared_id = $this->session->getSharedId();

                if (is_int($shared_id)) {
                    $this->is_logged = true;

                    $this->status = self::STATUS_AUTHENTICATED;
                } else {
                    $this->session->setSharedId($shared_id);

                    $this->status = self::STATUS_ANONYMOUS;
                }
            }

            $this->token_handler->setToken($this->token);
        } catch (AuthException $e) {
            $this->reset($e->getMessage());
        }
    }

    public function logout()
    {
        $this->reset(self::STATUS_SIGNED_OUT);
    }

    public function startAnonymousSession()
    {
        if ($this->is_logged || $this->isAnonymous()) {
            return;
        }

        $shared_id = Text::generateAlphabeticString(20);

        $this->session = $this->session_pool->get();
        $this->session->setSharedId($shared_id);

        $this->token_handler->setToken($this->session->getId());
    }

    /**
     * @param int $id
     * @return mixed
     */
    protected function retrieveUser($id)
    {
        $query = $this->options['model'] . 'Query';

        return $query::create()->findPk((int) $id);
    }

    /**
     * @param string $token
     * @return SessionEntry|null
     */
    protected function retrieveSessionEntry($token)
    {
        return SessionEntryQuery::create()->findOneByToken((string) $token);
    }

    protected function startUserSession()
    {
        if ($this->is_logged || $this->isAnonymous()) {
            return;
        }

        $this->session = $this->session_pool->get();
        $this->session->setSharedId($this->user->getId());

        $this->token = $this->session->getId();

        $this->session_entry = new SessionEntry();
        $this->session_entry->setToken($this->token);
        $this->session_entry->setModelId($this->user->getId());
        $this->session_entry->setModelName(get_class($this->user));

        $lifetime = $this->token_handler->getTokenLifetime() + $this->options['update_gap'];

        $expired_at = (new \DateTime())->modify('+' . $lifetime . ' second');

        $this->session_entry->setExpiredAt($expired_at);
        $this->session_entry->save();

        $this->token_handler->setToken($this->token);
    }

    protected function updateSessionEntry()
    {
        $this->session_entry = $this->getSessionEntry();

        if ($this->session_entry) {
            $lifetime = $this->token_handler->getTokenLifetime() + $this->options['update_gap'];

            $expired_at = (new \DateTime())->modify('+' . $lifetime . ' second');

            $this->session_entry->setExpiredAt($expired_at);
            $this->session_entry->save();
        }
    }

    /**
     * @deprecated
     */
    public function invalidateSessions($user = null)
    {
    }

    /**
     * @param mixed $user
     */
    public function destroySessions($user = null)
    {
        if ($user === null) {
            $user = $this->user;
        }

        $_sessions = SessionEntryQuery::create()
            ->filterByModelId($user->getId())
            ->filterByModelName(get_class($user))
            ->find();

        foreach ($_sessions as $_session) {
            $this->session_pool->get($_session->getToken())->destroy();
        }

        $_sessions->delete();
    }

    /**
     * @param $status
     */
    protected function reset($status)
    {
        $this->user = null;

        $this->status = $status;

        if ($this->session !== null) {
            $this->session->destroy();
        }

        if ($this->token !== null) {
            $this->token_handler->deleteToken();
        }
    }
}
