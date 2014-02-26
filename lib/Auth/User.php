<?php

namespace Perfumer\Auth;

use App\Model\Base\User as BaseUser;
use App\Model\Map\UserTableMap;

class User extends BaseUser
{
    protected $is_logged = false;
    protected $permissions = [];

    public function getIsLogged()
    {
        return $this->is_logged;
    }

    public function setIsLogged($is_logged)
    {
        $this->is_logged = (boolean) $is_logged;

        return $this;
    }

    public function setPassword($v)
    {
        $password = password_hash($v, PASSWORD_DEFAULT);

        if ($this->password !== $password)
        {
            $this->password = $password;
            $this->modifiedColumns[] = UserTableMap::PASSWORD;
        }

        return $this;
    }

    public function checkPassword($password)
    {
        return password_verify($password, $this->getPassword());
    }

    public function granted($permissions)
    {
        if (!$this->getIsLogged())
            return false;

        if ($this->getIsAdmin())
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
        }
    }
}