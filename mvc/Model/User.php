<?php

namespace Perfumer\MVC\Model;

use App\Model\Base\User as BaseUser;
use App\Model\DelegationQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;

class User extends BaseUser
{
    protected $profile;
    protected $is_logged = false;
    protected $role_ids = [];
    protected $permissions = [];

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

    public function getProfile()
    {
        if ($this->profile !== null)
            return $this->profile;

        if ($this->getGroupName() !== null && $this->getGroupId() !== null)
        {
            $query = 'App\\Model\\' . $this->getGroupName() . 'Query';

            $this->profile = $query::create()->findPk($this->getGroupId());
        }

        return $this->profile;
    }

    public function setLogged($is_logged)
    {
        $this->is_logged = (boolean) $is_logged;

        return $this;
    }

    public function isLogged()
    {
        return $this->is_logged;
    }

    public function inGroup($name)
    {
        return $this->getGroupName() === $name;
    }

    public function isGranted($permissions)
    {
        if (!$this->isLogged())
            return false;

        if ($this->isAdmin())
            return true;

        if (!is_array($permissions))
            $permissions = [$permissions];

        return array_intersect($this->permissions, $permissions);
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
        }

        $this->role_ids = $roles->getPrimaryKeys();
    }

    public function getRoleIds()
    {
        return $this->role_ids;
    }

    public function setRoleIds($role_ids)
    {
        $this->role_ids = (array) $role_ids;

        return $this;
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

    public function getDelegatedIds($model, $modifier = Delegation::MOD_EMPTY)
    {
        if (is_object($model))
        {
            $model = get_class($model);
        }
        else
        {
            $model = 'App\\Model\\' . $model;
        }

        return DelegationQuery::create()
            ->filterByRoleId($this->role_ids)
            ->filterByModelName($model)
            ->filterByModifier($modifier)
            ->select('model_id')
            ->find()
            ->getData();
    }

    public function getDelegatedObjects($model, $modifier = Delegation::MOD_EMPTY)
    {
        $delegated_ids = $this->getDelegatedIds($model, $modifier);

        if (is_object($model))
        {
            $model = get_class($model);
        }
        else
        {
            $model = 'App\\Model\\' . $model;
        }

        $model .= 'Query';

        return $model::create()->findPks($delegated_ids);
    }

    public function addDelegatedObjects(ObjectCollection $collection, $modifier = Delegation::MOD_EMPTY)
    {
        foreach ($collection as $model)
        {
            $object = DelegationQuery::create()
                ->filterByModelName(get_class($model))
                ->filterByModelId($model->getId())
                ->filterByModifier($modifier)
                ->findOneOrCreate();

            $object->save();
        }

        return $this;
    }

    public function setDelegatedObjects(ObjectCollection $collection, $modifier = Delegation::MOD_EMPTY)
    {
        DelegationQuery::create()
            ->filterByModelName(get_class($collection->get(0)))
            ->filterByModelId($collection->getPrimaryKeys(), Criteria::NOT_IN)
            ->filterByModifier($modifier)
            ->delete();

        $this->addDelegatedObjects($collection, $modifier);

        return $this;
    }

    public function deleteDelegatedObjects(ObjectCollection $collection, $modifier = Delegation::MOD_EMPTY)
    {
        DelegationQuery::create()
            ->filterByModelName(get_class($collection->get(0)))
            ->filterByModelId($collection->getPrimaryKeys())
            ->filterByModifier($modifier)
            ->delete();

        return $this;
    }
}