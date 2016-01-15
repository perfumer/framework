<?php

namespace Perfumer\Component\Auth;

use App\Model\Application;
use App\Model\ApplicationQuery;
use App\Model\Session as SessionEntry;
use App\Model\SessionQuery as SessionEntryQuery;
use Perfumer\Component\Auth\Exception\AuthException;
use Perfumer\Component\Auth\TokenHandler\AbstractHandler as TokenHandler;
use Perfumer\Component\Session\Core as SessionService;
use Perfumer\Component\Session\Item as SessionCell;
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
     * @var SessionService
     */
    protected $session_service;

    /**
     * @var SessionCell
     */
    protected $session;

    /**
     * @var TokenHandler
     */
    protected $token_handler;

    protected $user;

    /**
     * @var \App\Model\Application
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

    public function __construct(SessionService $session_service, TokenHandler $token_handler, $options = [])
    {
        $default_options = [
            'model' => '\\App\\Model\\User',
            'username_field' => 'username',
            'acl' => false,
            'application' => false,
            'update_gap' => 3600
        ];

        $this->options = array_merge($default_options, $options);

        $this->session_service = $session_service;
        $this->token_handler = $token_handler;
        $this->user = new $this->options['model']();

        $this->token = $this->token_handler->getToken();

        if ($this->token !== null)
            $this->session = $this->session_service->get($this->token);
    }

    public function isLogged()
    {
        if ($this->status === null)
            $this->init();

        return $this->user->isLogged();
    }

    public function getUser()
    {
        if ($this->status === null)
            $this->init();

        return $this->user;
    }

    public function getApplication()
    {
        if ($this->status === null)
            $this->init();

        return $this->application;
    }

    public function getStatus()
    {
        if ($this->status === null)
            $this->init();

        return $this->status;
    }

    public function getSession()
    {
        if ($this->session === null || $this->session->isDestroyed())
        {
            $this->session = $this->session_service->get();

            $this->token_handler->setToken($this->session->getId());
        }

        return $this->session;
    }

    public function getSessionEntry()
    {
        if ($this->session_entry === null && $this->isLogged())
            $this->session_entry = SessionEntryQuery::create()->findOneByToken($this->token);

        return $this->session_entry;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function init()
    {
        $regenerate_session = false;
        $update_session = false;

        try
        {
            if ($this->token === null)
                throw new AuthException(self::STATUS_NO_TOKEN);

            $user = null;
            $application = null;

            if (!$this->session_service->has($this->token))
            {
                $_session_entry = SessionEntryQuery::create()->findOneByToken($this->token);

                if (!$_session_entry)
                    throw new AuthException(self::STATUS_INVALID_TOKEN);

                if ($_session_entry->getExpiredAt() !== null && $_session_entry->getExpiredAt()->diff(new \DateTime())->invert == 0)
                    throw new AuthException(self::STATUS_EXPIRED_TOKEN);

                $user = $this->retrieveUser($_session_entry->getModelId());

                if (!$user)
                    throw new AuthException(self::STATUS_INVALID_USER);

                if ($this->options['application'])
                {
                    if ($_session_entry->getApplicationId() === null)
                    {
                        throw new AuthException(self::STATUS_NO_APPLICATION);
                    }
                    else
                    {
                        $application = $_session_entry->getApplication();
                    }
                }

                $this->session_entry = $_session_entry;

                $regenerate_session = true;
            }
            else
            {
                $_user = $this->session->get('_user');

                if (isset($_user['data']['id']))
                {
                    if ($this->options['application'])
                    {
                        $_application = $this->session->get('_application');

                        if (!isset($_application['data']) || !isset($_application['data']['id']))
                            throw new AuthException(self::STATUS_NO_APPLICATION);
                    }

                    if(time() - $this->session->get('_updated_at') >= $this->options['update_gap'])
                    {
                        $user = $this->retrieveUser($_user['data']['id']);

                        if (!$user)
                            throw new AuthException(self::STATUS_INVALID_USER);

                        if ($this->options['application'])
                        {
                            $application = ApplicationQuery::create()->findPk($_application['data']['id']);

                            if (!$application)
                                throw new AuthException(self::STATUS_INVALID_APPLICATION);
                        }

                        $update_session = true;
                    }
                    else
                    {
                        if ($_user['model'] !== $this->options['model'])
                            throw new AuthException(self::STATUS_INVALID_USER);

                        $user = new $this->options['model']();
                        $user->fromArray($_user['data'], TableMap::TYPE_FIELDNAME);
                        $user->setNew(false);

                        if ($this->options['acl']) {
                            $user->setPermissions($_user['permissions']);
                            $user->setRoleIds($_user['role_ids']);
                        }

                        if ($this->options['application'])
                        {
                            $application = new Application();
                            $application->fromArray($_application['data'], TableMap::TYPE_FIELDNAME);
                            $application->setNew(false);
                        }
                    }
                }
                else
                {
                    // This is anonymous user, but has some data in session
                    $this->status = self::STATUS_ANONYMOUS;

                    $this->token_handler->setToken($this->token);

                    return;
                }
            }

            if ($user->isDisabled())
                throw new AuthException(self::STATUS_ACCOUNT_DISABLED);

            if ($user->getBannedTill() !== null && $user->getBannedTill()->diff(new \DateTime())->invert == 0)
                throw new AuthException(self::STATUS_ACCOUNT_BANNED);

            $this->user = $user;
            $this->user->setLogged(true);

            $this->application = $application;

            if ($regenerate_session)
                $this->regenerateSession();

            if ($update_session)
                $this->updateSession();

            $this->status = self::STATUS_AUTHENTICATED;

            $this->token_handler->setToken($this->token);
        }
        catch (AuthException $e)
        {
            $this->reset($e->getMessage());
        }
    }

    public function logout()
    {
        $this->reset(self::STATUS_SIGNED_OUT);
    }

    protected function retrieveUser($id)
    {
        $query = $this->options['model'] . 'Query';

        return $query::create()->findPk($id);
    }

    protected function startSession()
    {
        if ($this->session !== null)
            $this->session->destroy();

        $this->session = $this->session_service->get();

        $this->updateSessionData();

        $this->token = $this->session->getId();

        $this->session_entry = new SessionEntry();
        $this->session_entry->setToken($this->token);
        $this->session_entry->setModelId($this->user->getId());
        $this->session_entry->setModelName($this->options['model']);

        if ($this->options['application'])
            $this->session_entry->setApplication($this->application);

        $lifetime = $this->token_handler->getTokenLifetime() + $this->options['update_gap'];

        $expired_at = (new \DateTime())->modify('+' . $lifetime . ' second');

        $this->session_entry->setExpiredAt($expired_at);
        $this->session_entry->save();
    }

    protected function regenerateSession()
    {
        if ($this->session !== null)
            $this->session->destroy();

        $this->session = $this->session_service->get();

        $this->updateSessionData();

        $this->token = $this->session->getId();

        $lifetime = $this->token_handler->getTokenLifetime() + $this->options['update_gap'];

        $expired_at = (new \DateTime())->modify('+' . $lifetime . ' second');

        $session_entry = new SessionEntry();
        $session_entry->fromArray($this->session_entry->toArray());
        $session_entry->setId(null);
        $session_entry->setToken($this->token);
        $session_entry->setCreatedAt(new \DateTime());
        $session_entry->setExpiredAt($expired_at);
        $session_entry->save();
    }

    protected function updateSession()
    {
        $this->session_entry = SessionEntryQuery::create()->findOneByToken($this->token);

        if ($this->session_entry)
        {
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

        if ($this->options['application'])
        {
            $_application = [
                'data' => $this->application->toArray(TableMap::TYPE_FIELDNAME)
            ];

            $this->session->set('_application', $_application);
        }
    }

    public function invalidateSessions($user = null)
    {
        if ($user === null)
            $user = $this->user;

        $_sessions = SessionEntryQuery::create()
            ->filterByModelId($user->getId())
            ->filterByModelName(get_class($user))
            ->filterByExpiredAt(new \DateTime(), '>=')
            ->find();

        foreach ($_sessions as $_session)
        {
            $session = $this->session_service->get($_session->getToken());

            if ($session->has('_updated_at'))
                $session->set('_updated_at', 0);
        }
    }

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