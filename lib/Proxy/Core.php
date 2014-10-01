<?php

namespace Perfumer\Proxy;

use Perfumer\Container\Core as Container;
use Perfumer\Proxy\Exception\ForwardException;
use Perfumer\Proxy\Exception\ProxyException;

class Core
{
    /**
     * @var Container
     */
    protected $container;

    protected $request_pool = [];

    /**
     * @var Request
     */
    protected $current_initial;

    /**
     * @var Request
     */
    protected $next;

    protected $input;

    protected $http_prefixes = [];
    protected $http_id;
    protected $http_query = [];
    protected $http_args = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $action = strtolower($_SERVER['REQUEST_METHOD']);

        if ($_SERVER['PATH_INFO'] == '/')
        {
            $url = $container->getParam('proxy.default_url');
        }
        else
        {
            $url = trim($_SERVER['PATH_INFO'], '/');
            $hyphen_pos = strpos($url, '-');

            if ($hyphen_pos !== false)
            {
                $this->http_id = substr($url, $hyphen_pos + 1);
                $url = substr($url, 0, $hyphen_pos);
            }

            if ($prefixes = $container->getParam('proxy.prefixes'))
            {
                $url = explode('/', $url);

                $prefix_values = array_slice($url, 0, count($prefixes));

                foreach ($prefixes as $key => $prefix)
                {
                    $this->http_prefixes[$prefix] = isset($prefix_values[$key]) ? $prefix_values[$key] : null;
                }

                if (count($prefixes) >= count($url))
                {
                    $url = $container->getParam('proxy.default_url');
                }
                else
                {
                    $url = array_slice($url, count($prefixes));
                    $url = implode('/', $url);
                }
            }
        }

        $this->next = $container->getService('request')->init($url, $action);

        $this->input = file_get_contents("php://input");

        $data_type = $container->getParam('proxy.data_type');

        // Get query parameters and args depending from type of data in the http request body
        if ($data_type == 'query_string')
        {
            switch ($action)
            {
                case 'get':
                    $this->http_query = $_GET;
                    break;
                case 'post':
                    $this->http_query = $_GET;
                    $this->http_args = $_POST;
                    break;
                default:
                    $this->http_query = $_GET;
                    parse_str($this->getInput(), $this->http_args);
                    break;
            }
        }
        else if ($data_type == 'json')
        {
            $this->http_query = $_GET;
            $this->http_args = $this->getInput() ? json_decode($this->getInput(), true) : [];

            if (!is_array($this->http_args))
                $this->http_args = [];
        }

        // Trim all args if auto_trim setting enabled
        if ($this->container->getParam('proxy.auto_trim'))
        {
            $this->http_args = $container->getService('arr')->trim($this->http_args);
        }

        // Convert empty strings to null values if auto_null setting enabled
        if ($this->container->getParam('proxy.auto_null'))
        {
            $this->http_args = $container->getService('arr')->convertValues($this->http_args, '', null);
        }
    }

    public function run()
    {
        $this->start()->send();
    }

    public function execute($url, $action, array $args = [])
    {
        $request = $this->container->getService('request')->init($url, $action, $args);

        return $this->executeController($request);
    }

    public function forward($url, $action, array $args = [])
    {
        $this->current_initial = null;

        $this->next = $this->container->getService('request')->init($url, $action, $args);

        throw new ForwardException();
    }

    public function getRequestPool()
    {
        return $this->request_pool;
    }

    public function getMain()
    {
        return $this->request_pool[0];
    }

    public function getInput()
    {
        return $this->input;
    }

    public function getPrefix($name = null, $default = null)
    {
        if ($name === null)
            return $this->http_prefixes;

        return isset($this->http_prefixes[$name]) ? $this->http_prefixes[$name] : $default;
    }

    public function setPrefix($name, $value)
    {
        if (!in_array($name, $this->container->getParam('proxy.prefixes')))
            throw new ProxyException('Prefix "' . $name . '" is not registered in configuration');

        $this->http_prefixes[$name] = $value;

        return $this;
    }

    public function getId()
    {
        return $this->http_id;
    }

    public function setId($id)
    {
        $this->http_id = $id;

        return $this;
    }

    public function getArg($name = null, $default = null)
    {
        if ($name === null)
            return $this->http_args;

        return isset($this->http_args[$name]) ? $this->http_args[$name] : $default;
    }

    public function hasArgs()
    {
        return count($this->http_args) > 0;
    }

    public function setArg($name, $value)
    {
        $this->http_args[$name] = $value;

        return $this;
    }

    public function setArgsArray($array)
    {
        $this->http_args = $array;

        return $this;
    }

    public function addArgsArray($array)
    {
        $this->http_args = array_merge($this->http_args, $array);

        return $this;
    }

    public function deleteArgs(array $keys = [])
    {
        if ($keys)
        {
            foreach ($keys as $key)
            {
                if (isset($this->http_args[$key]))
                    unset($this->http_args[$key]);
            }
        }
        else
        {
            $this->http_args = [];
        }

        return $this;
    }

    public function getQuery($name = null, $default = null)
    {
        if ($name === null)
            return $this->http_query;

        return isset($this->http_query[$name]) ? $this->http_query[$name] : $default;
    }

    public function hasQuery()
    {
        return count($this->http_query) > 0;
    }

    public function setQuery($name, $value)
    {
        $this->http_query[$name] = $value;

        return $this;
    }

    public function setQueryArray($array)
    {
        $this->http_query = $array;

        return $this;
    }

    public function addQueryArray($array)
    {
        $this->http_query = array_merge($this->http_query, $array);

        return $this;
    }

    public function deleteQuery(array $keys = [])
    {
        if ($keys)
        {
            foreach ($keys as $key)
            {
                if (isset($this->http_query[$key]))
                    unset($this->http_query[$key]);
            }
        }
        else
        {
            $this->http_query = [];
        }

        return $this;
    }

    public function generateUrl($url, $id = null, $query = [], $prefixes = [])
    {
        $generated_url = '/' . trim($url, '/');

        if ($this->container->getParam('proxy.prefixes'))
        {
            if ($prefixes)
            {
                $prefixes = $this->container->getService('arr')->fetch($prefixes, $this->container->getParam('proxy.prefixes'));
                $prefixes = array_merge($this->getPrefix(), $prefixes);
            }
            else
            {
                $prefixes = $this->getPrefix();
            }

            $generated_url = '/' . implode('/', $prefixes) . $generated_url;
        }

        if ($id)
            $generated_url .= '-' . $id;

        if ($query)
        {
            $query_parts = [];

            foreach ($query as $key => $value)
                $query_parts[] = $key . '=' . urlencode($value);

            $generated_url .= '?' . implode('&', $query_parts);
        }

        return $generated_url;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function start()
    {
        try
        {
            $response = $this->executeController($this->next);
        }
        catch (ForwardException $e)
        {
            return $this->start();
        }

        return $response;
    }

    protected function executeController(Request $request)
    {
        $this->request_pool[] = $request;

        if ($this->current_initial === null)
        {
            $this->current_initial = $request;
        }
        else
        {
            $request->setInitial($this->current_initial);
        }

        try
        {
            $reflection_class = new \ReflectionClass($request->getController());
        }
        catch (\ReflectionException $e)
        {
            $this->forward('exception/page', 'controllerNotFound');
        }

        $response = $this->container->getService('response');

        $controller = $reflection_class->newInstance($this->container, $request, $response, $reflection_class);

        return $reflection_class->getMethod('execute')->invoke($controller);
    }
}