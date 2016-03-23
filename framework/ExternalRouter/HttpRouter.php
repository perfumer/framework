<?php

namespace Perfumer\Framework\ExternalRouter;

use Perfumer\Helper\Arr;
use Perfumer\Framework\Proxy\Exception\ProxyException;
use Perfumer\Framework\Proxy\Response;
use Symfony\Component\HttpFoundation\Response as ExternalResponse;

class HttpRouter implements RouterInterface
{
    /**
     * @var ExternalResponse
     */
    protected $response;

    protected $input;

    protected $options = [];

    protected $http_prefixes = [];
    protected $http_id;
    protected $http_id_array;
    protected $http_query = [];
    protected $http_args = [];

    public function __construct($options = [])
    {
        $default_options = [
            'auto_null' => true,
            'auto_trim' => true,
            'data_type' => 'query_string',
            'default_url' => 'home',
            'prefixes' => [],
            'prefix_options' => [],
            'allowed_actions' => ['get', 'post', 'head', 'options'],
            'not_found_attributes' => ['framework', 'exception/template', 'controllerNotFound']
        ];

        $this->options = array_merge($default_options, $options);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'http_router';
    }

    /**
     * @return array
     */
    public function getAllowedActions()
    {
        return $this->options['allowed_actions'];
    }

    /**
     * @return array
     */
    public function getNotFoundAttributes()
    {
        return $this->options['not_found_attributes'];
    }

    /**
     * @return bool
     */
    public function isHttp()
    {
        return true;
    }

    public function dispatch()
    {
        $action = strtolower($_SERVER['REQUEST_METHOD']);

        if ($prefixes = $this->options['prefixes'])
        {
            $this->http_prefixes = array_fill_keys($prefixes, null);

            $prefix_options = $this->options['prefix_options'];

            if (is_array($prefix_options))
            {
                foreach ($prefixes as $prefix)
                {
                    if (isset($prefix_options[$prefix]['default_value']))
                        $this->http_prefixes[$prefix] = $prefix_options[$prefix]['default_value'];
                }
            }
        }

        if ($_SERVER['PATH_INFO'] == '/')
        {
            $url = $this->options['default_url'];
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

            if ($prefixes = $this->options['prefixes'])
            {
                $url = explode('/', $url);

                $prefix_options = $this->options['prefix_options'];

                while ($prefixes && $url)
                {
                    $one_prefix = array_shift($prefixes);
                    $one_url = $url[0];

                    if ($this->validateUrlPartForPrefix($one_prefix, $one_url, $prefix_options))
                    {
                        $this->http_prefixes[$one_prefix] = $one_url;

                        array_shift($url);
                    }
                }

                $url = count($url) > 0 ? implode('/', $url) : $this->options['default_url'];
            }
        }

        $this->input = file_get_contents("php://input");

        $data_type = $this->options['data_type'];

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
        if ($this->options['auto_trim'])
        {
            $this->http_args = Arr::trim($this->http_args);
        }

        // Convert empty strings to null values if auto_null setting enabled
        if ($this->options['auto_null'])
        {
            $this->http_args = Arr::convertValues($this->http_args, '', null);
        }

        return [$url, $action, []];
    }

    public function getExternalResponse()
    {
        if ($this->response === null)
            $this->response = new ExternalResponse();

        return $this->response;
    }

    public function sendResponse(Response $response)
    {
        $this->getExternalResponse()->setContent($response->getContent())->send();
    }

    public function generateUrl($url, $id = null, $query = [], $prefixes = [])
    {
        $generated_url = trim($url, '/');

        if ($generated_url)
            $generated_url = '/' . $generated_url;

        if ($this->options['prefixes'])
        {
            if ($prefixes)
            {
                $prefixes = Arr::fetch($prefixes, $this->options['prefixes']);
                $prefixes = array_merge($this->getPrefix(), $prefixes);
            }
            else
            {
                $prefixes = $this->getPrefix();
            }

            $generated_url = '/' . implode('/', $prefixes) . $generated_url;
        }

        if ($id)
            $generated_url .= is_array($id) ? '-' . implode('/', $id) : '-' . $id;

        if ($query)
        {
            $query_string = http_build_query($query, '', '&');

            if ($query_string)
                $generated_url .= '?' . $query_string;
        }

        if (!$generated_url)
            $generated_url = '/';

        return $generated_url;
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
        if (!in_array($name, $this->options['prefixes']))
            throw new ProxyException('Prefix "' . $name . '" is not registered in configuration');

        $this->http_prefixes[$name] = $value;

        return $this;
    }

    public function getId($index = null)
    {
        if ($index === null)
            return $this->http_id;

        if ($this->http_id_array === null)
            $this->http_id_array = explode('/', $this->http_id);

        return isset($this->http_id_array[$index]) ? $this->http_id_array[$index] : null;
    }

    public function setId($id, $index = null)
    {
        if ($index === null)
        {
            $this->http_id = $id;
        }
        else
        {
            if ($this->http_id_array === null)
                $this->http_id_array = explode('/', $this->http_id);

            $this->http_id_array[$index] = $id;
        }

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

    protected function validateUrlPartForPrefix($prefix, $part, $options)
    {
        if ($options === null || !isset($options[$prefix]))
            return true;

        $options = $options[$prefix];

        if (isset($options['white_list']))
            return in_array($part, $options['white_list']);

        if (isset($options['black_list']))
            return !in_array($part, $options['black_list']);

        if (isset($options['regex']))
            return preg_match($options['regex'], $part);

        return true;
    }
}
