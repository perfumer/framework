<?php

namespace Perfumer\Component\Auth;

trait UserHelpers
{
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
            $permission = $role->getPermission();
            $permission = explode('.', $permission);

            for ($i = 1; $i <= count($permission); $i++)
            {
                $sub_permission = array_slice($permission, 0, $i);
                $sub_permission = implode('.', $sub_permission);

                if (!in_array($sub_permission, $this->permissions))
                    $this->permissions[] = $sub_permission;
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
}