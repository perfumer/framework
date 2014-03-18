<?php

namespace Perfumer\Helper;

use Perfumer\Helper\Exception\HelperException;

/**
 * Fork of Kohana_Feed class, written in non-static way.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Feed
{
    public function parse($feed, $limit = 0)
    {
        if ( ! function_exists('simplexml_load_file'))
            throw new HelperException('SimpleXML must be installed!');

        $limit = (int) $limit;

        $error_level = error_reporting(0);

        $feed = file_get_contents($feed);
        $feed = simplexml_load_string($feed, 'SimpleXMLElement', LIBXML_NOCDATA);

        error_reporting($error_level);

        if ($feed === false)
            return [];

        $namespaces = $feed->getNamespaces(true);

        $feed = isset($feed->channel) ? $feed->xpath('//item') : $feed->entry;

        $i = 0;
        $items = [];

        foreach ($feed as $item)
        {
            if ($limit > 0 and $i++ === $limit)
                break;

            $item_fields = (array) $item;

            foreach ($namespaces as $ns)
                $item_fields += (array) $item->children($ns);

            $items[] = $item_fields;
        }

        return $items;
    }
}