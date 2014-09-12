<?php

namespace Perfumer\Model;

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

    public function revealRoles()
    {
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

                $array = &$this->delegations[$key];

                if (!isset($array[$delegation->getType()]))
                    $array[$delegation->getType()] = [];

                $array = &$array[$delegation->getType()];

                $array = array_merge($array, $delegation->getModelIds());
                $array = array_unique($array);
            }
        }
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = (array) $permissions;

        return $this;
    }

    public function getDelegations()
    {
        return $this->delegations;
    }

    public function setDelegations($delegations)
    {
        $this->delegations = (array) $delegations;

        return $this;
    }

    public function getDelegatedIds($model, $type = Delegation::TYPE_COMMON)
    {
        if (is_object($model))
        {
            $model = get_class($model);
        }
        else
        {
            $model = 'App\\Model\\' . $model;
        }

        if (!isset($this->delegations[$model][$type]))
            return [];

        return $this->delegations[$model][$type];
    }

    public function getDelegatedObjects($model, $type = Delegation::TYPE_COMMON)
    {
        $ids = $this->getDelegatedIds($model, $type);

        if (!$ids)
            return new ObjectCollection();

        if (is_object($model))
        {
            $model = get_class($model);
        }
        else
        {
            $model = '\\App\\Model\\' . $model . 'Query';
        }

        return $model::create()->findPks($ids);
    }
}