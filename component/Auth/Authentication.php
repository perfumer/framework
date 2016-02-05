<?php

namespace Perfumer\Component\Auth;

use App\Model\Application;
use App\Model\ApplicationQuery;
use App\Model\Session as SessionEntry;
use App\Model\SessionQuery as SessionEntryQuery;
use Perfumer\Component\Auth\Exception\AuthException;
use Perfumer\Component\Auth\TokenHandler\AbstractHandler as TokenHandler;
use Perfumer\Component\Session\Pool as SessionPool;
use Perfumer\Component\Session\Session;
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

    protected $user;

    /**
     * @var Application
     */
    protected $application;

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
            'application' => false,
            'update_gap' => 3600
        ];

        $this->options = array_merge($default_options, $options);

        $this->session_pool = $session_pool;
        $this->token_handler = $token_handler;
        $this->user = new $this->options['model']();
        $this->token = $this->token_handler->getToken();
    }

    /**
     * @return bool
     */
    public function isLogged()
    {
        $this->init();

        return $this->user->isLogged();
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        $this->init();

        return $this->user;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        $this->init();

        return $this->application;
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

        if ($this->session === null) {
            $this->session = $this->session_pool->get();

            $this->token_handler->setToken($this->session->getId());
        }

        return $this->session;
    }

    /**
     * @return SessionEntry|null
     */
    public function getSessionEntry()
    {
        $this->init();

        if ($this->session_entry === null && $this->isLogged()) {
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
        $update_session = false;

        try {
            if ($this->token === null) {
                throw new AuthException(self::STATUS_NO_TOKEN);
            }

            $user = null;
            $application = null;
            $session = $this->session_pool->get($this->token);

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

                if ($this->options['application']) {
                    if ($_session_entry->getApplicationId() === null) {
                        throw new AuthException(self::STATUS_NO_APPLICATION);
                    } else {
                        $application = $_session_entry->getApplication();
                    }
                }

                $this->session_entry = $_session_entry;

                $update_session = true;
            } else {
                $_user = $session->get('_user');

                if (!isset($_user['data']) || !isset($_user['data']['id'])) {
                    // This is anonymous user, but has some data in session
                    $this->status = self::STATUS_ANONYMOUS;

                    $this->token_handler->setToken($this->token);

                    return;
                } else {
                    if ($this->options['application']) {
                        $_application = $session->get('_application');

                        if (!isset($_application['data']) || !isset($_application['data']['id'])) {
                            throw new AuthException(self::STATUS_NO_APPLICATION);
                        }
                    }

                    if(time() - $session->get('_updated_at') >= $this->options['update_gap']) {
                        $user = $this->retrieveUser($_user['data']['id']);

                        if (!$user) {
                            throw new AuthException(self::STATUS_INVALID_USER);
                        }

                        if ($this->options['application']) {
                            $application = $this->retrieveApplication($_application['data']['id']);

                            if (!$application) {
                                throw new AuthException(self::STATUS_INVALID_APPLICATION);
                            }
                        }

                        $update_session = true;
                    } else {
                        if ($_user['model'] !== $this->options['model']) {
                            throw new AuthException(self::STATUS_INVALID_USER);
                        }

                        $user = new $this->options['model']();
                        $user->fromArray($_user['data'], TableMap::TYPE_FIELDNAME);
                        $user->setNew(false);

                        if ($this->options['acl']) {
                            $user->setPermissions($_user['permissions']);
                            $user->setRoleIds($_user['role_ids']);
                        }

                        if ($this->options['application']) {
                            $application = new Application();
                            $application->fromArray($_application['data'], TableMap::TYPE_FIELDNAME);
                            $application->setNew(false);
                        }
                    }
                }
            }

            if ($user->isDisabled()) {
                throw new AuthException(self::STATUS_ACCOUNT_DISABLED);
            }

            if ($user->getBannedTill() !== null && $user->getBannedTill()->diff(new \DateTime())->invert == 0) {
                throw new AuthException(self::STATUS_ACCOUNT_BANNED);
            }

            $this->user = $user;
            $this->user->setLogged(true);

            $this->application = $application;
            $this->session = $session;

            $this->status = self::STATUS_AUTHENTICATED;

            $this->token_handler->setToken($this->token);

            if ($update_session) {
                $this->updateSession();
            }
        } catch (AuthException $e) {
            $this->reset($e->getMessage());
        }
    }

    public function logout()
    {
        $this->reset(self::STATUS_SIGNED_OUT);
    }

    /**
     * @param int $id
     * @return mixed
     */
    protected function retrieveUser($id)
    {
        $query = $this->options['model'] . 'Query';

        return $query::create()->findPk($id);
    }

    /**
     * @param int $id
     * @return Application|null
     */
    protected function retrieveApplication($id)
    {
        return ApplicationQuery::create()->findPk($id);
    }

    /**
     * @param string $token
     * @return SessionEntry|null
     */
    protected function retrieveSessionEntry($token)
    {
        return SessionEntryQuery::create()->findOneByToken($token);
    }

    protected function startSession()
    {
        if ($this->session !== null)
            $this->session->destroy();

        $this->session = $this->session_pool->get();

        $this->updateSessionData();

        $this->token = $this->session->getId();

        $this->session_entry = new SessionEntry();
        $this->session_entry->setToken($this->token);
        $this->session_entry->setModelId($this->user->getId());
        $this->session_entry->setModelName(get_class($this->user));

        if ($this->options['application']) {
            $this->session_entry->setApplication($this->application);
        }

        $lifetime = $this->token_handler->getTokenLifetime() + $this->options['update_gap'];

        $expired_at = (new \DateTime())->modify('+' . $lifetime . ' second');

        $this->session_entry->setExpiredAt($expired_at);
        $this->session_entry->save();
    }

    protected function updateSession()
    {
        $this->session_entry = $this->getSessionEntry();

        if ($this->session_entry) {
            $lifetime = $this->token_handler->getTokenLifetime() + $this->options['update_gap'];

            $expired_at = (new \DateTime())->modify('+' . $lifetime . ' second');

            $this->session_entry->setExpiredAt($expired_at);
            $this->session_entry->save();
        }

        $this->updateSessionData();
    }

    protected function updateSessionData()
    {
        $_user = [
            'model' => $this->options['model'],
            'data' => $this->user->toArray(TableMap::TYPE_FIELDNAME)
        ];

        if ($this->options['acl']) {
            $this->user->revealRoles();

            $_user['permissions'] = $this->user->getPermissions();
            $_user['role_ids'] = $this->user->getRoleIds();
        }

        $this->session->set('_user', $_user)->set('_updated_at', time());

        if ($this->options['application']) {
            $_application = [
                'data' => $this->application->toArray(TableMap::TYPE_FIELDNAME)
            ];

            $this->session->set('_application', $_application);
        }
    }

    /**
     * @param $user
     */
    public function invalidateSessions($user = null)
    {
        if ($user === null)
            $user = $this->user;

        $_sessions = SessionEntryQuery::create()
            ->filterByModelId($user->getId())
            ->filterByModelName(get_class($user))
            ->filterByExpiredAt(new \DateTime(), '>=')
            ->find();

        foreach ($_sessions as $_session) {
            $this->session_pool->get($_session->getToken())->destroy();
        }
    }

    /**
     * @param $status
     */
    protected function reset($status)
    {
        $this->user = new $this->options['model']();
        $this->application = null;

        $this->status = $status;

        if ($this->session !== null) {
            $this->session->destroy();
        }

        if ($this->token !== null) {
            $this->token_handler->deleteToken();
        }
    }
}
