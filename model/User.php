<?php

namespace Perfumer\Model;

use App\Model\Base\User as BaseUser;
use App\Model\DelegationQuery;
use App\Model\UserGroupQuery;
use Perfumer\Helper\Arr;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;

class User extends BaseUser
{
    protected $profiles = [];
    protected $is_logged = false;
    protected $role_ids = [];
    protected $permissions = [];

    public function getProfile($group_name)
    {
        if (array_key_exists($group_name, $this->profiles))
            return $this->profiles[$group_name];

        $user_group = UserGroupQuery::create()
            ->filterByUserId($this->getId())
            ->filterByGroupName($group_name)
            ->findOne();

        if ($user_group)
        {
            $query = 'App\\Model\\' . $group_name . 'Query';

            $this->profiles[$group_name] = $query::create()->findPk($user_group->getGroupId());
        }
        else
        {
            $this->profiles[$group_name] = null;
        }

        return $this->profiles[$group_name];
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

    public function ban($seconds)
    {
        $date = (new \DateTime())->modify('+' . $seconds . ' second');

        $this->setBannedTill($date);

        return $this;
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

    public function inGroup($groups)
    {
        if (!is_array($groups))
            $groups = [$groups];

        if (array_filter(Arr::fetch($this->profiles, $groups)))
            return true;

        $user_group = UserGroupQuery::create()
            ->filterByUserId($this->getId())
            ->filterByGroupName($groups, Criteria::IN)
            ->findOne();

        return (bool) $user_group;
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