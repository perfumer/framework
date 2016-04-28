<?php

namespace Perfumer\Framework\Router\Http;

use Perfumer\Framework\Bundle\Resolver\HttpResolver;
use Perfumer\Framework\Router\RouterInterface;
use Perfumer\Helper\Arr;
use Perfumer\Framework\Proxy\Exception\ProxyException;
use Perfumer\Framework\Proxy\Response;
use Symfony\Component\HttpFoundation\Response as ExternalResponse;

class DefaultRouter implements RouterInterface
{
    /**
     * @var HttpResolver
     */
    protected $bundle_resolver;

    /**
     * @var ExternalResponse
     */
    protected $response;

    protected $input;

    protected $options = [];

    protected $http_prefixes = [];
    protected $http_id;
    protected $http_id_array;
    protected $http_fields = [];
    protected $http_query = [];
    protected $http_args = [];

    public function __construct(HttpResolver $bundle_resolver, $options = [])
    {
        $this->bundle_resolver = $bundle_resolver;

        $default_options = [
            'auto_null' => true,
            'auto_trim' => true,
            'data_type' => 'query_string',
            'default_url' => 'home',
            'prefixes' => [],
            'prefix_options' => [],
            'allowed_actions' => ['get', 'post', 'head', 'options'],
            'not_found_attributes' => ['framework/http', 'exception/template', 'controllerNotFound']
        ];

        $this->options = array_merge($default_options, $options);
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

    /**
     * @return array
     */
    public function dispatch()
    {
        $action = strtolower($_SERVER['REQUEST_METHOD']);

        if ($prefixes = $this->options['prefixes']) {
            $this->http_prefixes = array_fill_keys($prefixes, null);

            $prefix_options = $this->options['prefix_options'];

            if (is_array($prefix_options)) {
                foreach ($prefixes as $prefix) {
                    if (isset($prefix_options[$prefix]['default_value'])) {
                        $this->http_prefixes[$prefix] = $prefix_options[$prefix]['default_value'];
                    }
                }
            }
        }

        if ($_SERVER['PATH_INFO'] == '/') {
            $url = $this->options['default_url'];
        } else {
            $url = trim($_SERVER['PATH_INFO'], '/');

            // Try to define prefixes
            if ($prefixes = $this->options['prefixes']) {
                $url = explode('/', $url);

                $prefix_options = $this->options['prefix_options'];

                while ($prefixes && $url) {
                    $one_prefix = array_shift($prefixes);
                    $one_url = $url[0];

                    if ($this->validateUrlPartForPrefix($one_prefix, $one_url, $prefix_options)) {
                        $this->http_prefixes[$one_prefix] = $one_url;

                        array_shift($url);
                    }
                }

                $url = count($url) > 0 ? implode('/', $url) : $this->options['default_url'];
            }

            // Try to define id
            if ($url) {
                if ((int) $url > 0) {
                    $this->http_id = $url;
                } else {
                    preg_match('/\/[0-9]+/', $url, $matches, PREG_OFFSET_CAPTURE);

                    if (isset($matches[0])) {
                        $slash_position = $matches[0][1];

                        $this->http_id = substr($url, $slash_position + 1);

                        if ($slash_position > 0) {
                            $url = substr($url, 0, $slash_position);
                        }
                    }
                }
            }
        }

        $this->input = file_get_contents("php://input");

        $data_type = $this->options['data_type'];

        // Get query parameters and args depending from type of data in the http request body
        if ($data_type == 'query_string') {
            switch ($action) {
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
        } else if ($data_type == 'json') {
            $this->http_query = $_GET;
            $this->http_args = $this->getInput() ? json_decode($this->getInput(), true) : [];

            if (!is_array($this->http_args)) {
                $this->http_args = [];
            }
        }

        // Trim all args if auto_trim setting enabled
        if ($this->options['auto_trim']) {
            $this->http_args = Arr::trim($this->http_args);
        }

        // Convert empty strings to null values if auto_null setting enabled
        if ($this->options['auto_null']) {
            $this->http_args = Arr::convertValues($this->http_args, '', null);
        }

        $this->http_fields = array_merge($this->http_query, $this->http_args);

        return [$url, $action, []];
    }

    /**
     * @return ExternalResponse
     */
    public function getExternalResponse()
    {
        if ($this->response === null) {
            $this->response = new ExternalResponse();
        }

        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function sendResponse(Response $response)
    {
        $this->getExternalResponse()->setContent($response->getContent())->send();
    }

    /**
     * @param string $url
     * @param mixed $id
     * @param array $query
     * @param array $prefixes
     * @return string
     */
    public function generateUrl($url, $id = null, $query = [], $prefixes = [])
    {
        $generated_url = trim($url, '/');

        if ($generated_url) {
            $generated_url = '/' . $generated_url;
        }

        if ($this->options['prefixes']) {
            if ($prefixes) {
                $prefixes = Arr::fetch($prefixes, $this->options['prefixes']);
                $prefixes = array_merge($this->getPrefix(), $prefixes);
            } else {
                $prefixes = $this->getPrefix();
            }

            $generated_url = '/' . implode('/', $prefixes) . $generated_url;
        }

        if ($id) {
            $generated_url .= is_array($id) ? '/' . implode('/', $id) : '/' . $id;
        }

        if ($query) {
            $query_string = http_build_query($query, '', '&');

            if ($query_string) {
                $generated_url .= '?' . $query_string;
            }
        }

        $generated_url = $this->bundle_resolver->getPrefix() . $generated_url;

        if (!$generated_url) {
            $generated_url = '/';
        }

        return $generated_url;
    }

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getPrefix($name = null, $default = null)
    {
        if ($name === null) {
            return $this->http_prefixes;
        }

        return isset($this->http_prefixes[$name]) ? $this->http_prefixes[$name] : $default;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     * @throws ProxyException
     */
    public function setPrefix($name, $value)
    {
        if (!in_array($name, $this->options['prefixes'])) {
            throw new ProxyException('Prefix "' . $name . '" is not registered in configuration');
        }

        $this->http_prefixes[$name] = $value;

        return $this;
    }

    /**
     * @param int|null $index
     * @return mixed
     */
    public function getId($index = null)
    {
        if ($index === null) {
            return $this->http_id;
        }

        if ($this->http_id_array === null) {
            $this->http_id_array = explode('/', $this->http_id);
        }

        return isset($this->http_id_array[$index]) ? $this->http_id_array[$index] : null;
    }

    /**
     * @param mixed $id
     * @param int|null $index
     * @return $this
     */
    public function setId($id, $index = null)
    {
        if ($index === null) {
            $this->http_id = $id;
        } else {
            if ($this->http_id_array === null) {
                $this->http_id_array = explode('/', $this->http_id);
            }

            $this->http_id_array[$index] = $id;
        }

        return $this;
    }

    /**
     * @param string|array|null $keys
     * @param mixed $default
     * @return mixed
     */
    public function getFields($keys = null, $default = null)
    {
        if ($keys === null) {
            return $this->http_fields;
        } elseif (is_array($keys)) {
            return Arr::fetch($this->http_fields, $keys, true, $default);
        } else {
            return isset($this->http_fields[$keys]) ? $this->http_fields[$keys] : $default;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setField($key, $value)
    {
        $this->http_fields[$key] = $value;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->http_fields = array_merge($this->http_fields, $fields);
    }

    /**
     * @param null $name
     * @param null $default
     * @return array|null
     * @deprecated
     */
    public function getArg($name = null, $default = null)
    {
        if ($name === null) {
            return $this->http_args;
        }

        return isset($this->http_args[$name]) ? $this->http_args[$name] : $default;
    }

    /**
     * @return bool
     * @deprecated
     */
    public function hasArgs()
    {
        return count($this->http_args) > 0;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     * @deprecated
     */
    public function setArg($name, $value)
    {
        $this->http_args[$name] = $value;

        return $this;
    }

    /**
     * @param $array
     * @return $this
     * @deprecated
     */
    public function setArgsArray($array)
    {
        $this->http_args = $array;

        return $this;
    }

    /**
     * @param $array
     * @return $this
     * @deprecated
     */
    public function addArgsArray($array)
    {
        $this->http_args = array_merge($this->http_args, $array);

        return $this;
    }

    /**
     * @param array $keys
     * @return $this
     * @deprecated
     */
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

    /**
     * @param null $name
     * @param null $default
     * @return array|null
     * @deprecated
     */
    public function getQuery($name = null, $default = null)
    {
        if ($name === null)
            return $this->http_query;

        return isset($this->http_query[$name]) ? $this->http_query[$name] : $default;
    }

    /**
     * @return bool
     * @deprecated
     */
    public function hasQuery()
    {
        return count($this->http_query) > 0;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     * @deprecated
     */
    public function setQuery($name, $value)
    {
        $this->http_query[$name] = $value;

        return $this;
    }

    /**
     * @param $array
     * @return $this
     * @deprecated
     */
    public function setQueryArray($array)
    {
        $this->http_query = $array;

        return $this;
    }

    /**
     * @param $array
     * @return $this
     * @deprecated
     */
    public function addQueryArray($array)
    {
        $this->http_query = array_merge($this->http_query, $array);

        return $this;
    }

    /**
     * @param array $keys
     * @return $this
     * @deprecated
     */
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
