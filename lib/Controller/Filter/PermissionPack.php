<?php

namespace Perfumer\Controller\Filter;

use Perfumer\Controller\Exception\FilterException;
use Perfumer\Controller\Exception\HTTPException;

trait PermissionPack
{
    protected function filterActionExists($action, $mode)
    {
        if (!method_exists($this, $action))
        {
            $this->filter_vars['action'] = $action;

            throw new FilterException('actionExists' . strtoupper($mode));
        }
    }

    protected function filterIsLogged($mode)
    {
        if (!$this->user->isLogged())
            throw new FilterException('isLogged' . strtoupper($mode));
    }

    protected function filterIsAdmin($mode)
    {
        $this->filterIsLogged($mode);

        if (!$this->user->getIsAdmin())
            throw new FilterException('isAdmin' . strtoupper($mode));
    }

    protected function filterIsGranted($permissions, $mode)
    {
        $this->filterIsLogged($mode);

        if (!$this->user->isGranted($permissions))
            throw new FilterException('isGranted' . strtoupper($mode));
    }

    public function filterActionExistsHTMLExceptionHandler()
    {
        throw new HTTPException("Action '{$this->filter_vars['action']}' does not exist", 404);
    }

    public function filterActionExistsJSONExceptionHandler()
    {
        $this->request->setTemplate('layout/json.twig');

        $this->addViewVars([
            'status' => 0,
            'message' => "Action '{$this->filter_vars['action']}' does not exist"
        ]);
    }

    public function filterIsLoggedHTMLExceptionHandler()
    {
        $login_url = $this->container->s('url.login');

        $this->request->setTemplate($login_url . '/get.twig');
        $this->request->setCSS($login_url . '/get.css');
        $this->request->setJS($login_url . '/get.js');
    }

    public function filterIsLoggedJSONExceptionHandler()
    {
        $this->request->setTemplate('layout/json.twig');

        $this->addViewVars([
            'status' => 0,
            'message' => 'Action is permitted to logged in users only'
        ]);
    }

    public function filterIsAdminHTMLExceptionHandler()
    {
        throw new HTTPException('Action is permitted to administrators only', 403);
    }

    public function filterIsAdminJSONExceptionHandler()
    {
        $this->request->setTemplate('layout/json.twig');

        $this->addViewVars([
            'status' => 0,
            'message' => 'Action is permitted to administrators only'
        ]);
    }

    public function filterIsGrantedHTMLExceptionHandler()
    {
        throw new HTTPException('You do not have enough rights for the action', 403);
    }

    public function filterIsGrantedJSONExceptionHandler()
    {
        $this->request->setTemplate('layout/json.twig');

        $this->addViewVars([
            'status' => 0,
            'message' => 'You do not have enough rights for the action'
        ]);
    }
}