<?php

namespace Perfumer\Auth;

use App\Model\Base\User as BaseUser;
use Propel\Runtime\Collection\ObjectCollection;

class User extends BaseUser
{
    protected $is_logged = false;
    protected $permissions = [];
    protected $delegations = [];

    public function isLogged()
    {
        return $this->is_logged;
    }

    public function setLogged($is_logged)
    {
        $this->is_logged = (boolean) $is_logged;

        return $this;
    }

    public function hashPassword($v)
    {
        $password = password_hash($v, PASSWORD_DEFAULT);

        $this->setPassword($password);

        return $this;
    }

    public function validatePassword($password)
    {
        return password_verify($password, $this->getPassword());
    }

    public function isGranted($permissions)
    {
        if (!$this->isLogged())
            return false;

        if ($this->isAdmin())
            return true;

        if (!is_array($permissions))
            $permissions = [$permissions];

        foreach ($permissions as $permission)
        {
            if (in_array($permission, $this->permissions))
                return true;
        }

        return false;
    }

    public function loadPermissions()
    {
        $this->permissions = [];

        $roles = $this->getRoles();

        foreach ($roles as $role)
        {
            $permissions = $role->getPermissions();

            foreach ($permissions as $permission)
            {
                $token = $permission->getToken();
                $token = explode('.', $token);

                for ($i = 1; $i <= count($token); $i++)
                {
                    $sub_token = array_slice($token, 0, $i);
                    $sub_token = implode('.', $sub_token);

                    if (!in_array($sub_token, $this->permissions))
                        $this->permissions[] = $sub_token;
                }
            }

            $delegations = $role->getDelegations();

            foreach ($delegations as $delegation)
            {
                $key = $delegation->getModelName();

                if (!isset($this->delegations[$key]))
                    $this->delegations[$key] = [];

                $this->delegations[$key] = array_merge($this->delegations[$key], $delegation->getModelIds());
                $this->delegations[$key] = array_unique($this->delegations[$key]);
            }
        }
    }

    public function getDelegatedIds($model)
    {
        if (is_object($model))
            $model = get_class($model);

        if (!isset($this->delegations[$model]))
            return [];

        return $this->delegations[$model];
    }

    public function getDelegatedObjects($model)
    {
        if (is_object($model))
            $model = get_class($model);

        $ids = $this->getDelegatedIds($model);

        if (!$ids)
            return new ObjectCollection();

        $model = $model . 'Query';

        return $model::create()->findPks($ids);
    }
}