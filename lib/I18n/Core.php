<?php

namespace Perfumer\I18n;

use App\Model\I18nQuery;
use Perfumer\I18n\Exception\I18nException;
use Stash\Pool;

class Core
{
    protected $cache;

    protected $translations = [];
    protected $locale;
    protected $active_group;

    public function __construct(Pool $cache, array $options)
    {
        $this->cache = $cache;

        if (isset($options['locale']))
            $this->locale = (string) $options['locale'];
    }

    public function translate($key, $placeholders = [])
    {
        if ($this->locale === null)
            throw new I18nException('Translation locale is not defined.');

        list($group, $name) = $this->extractTranslationKey($key);

        if ($name === null && $this->active_group === null)
            throw new I18nException('Active group is not set for using short syntax or you have forgotten to specify name of translation phrase.');

        if ($name === null)
        {
            $name = $group;
            $group = $this->active_group;
        }

        if (!isset($this->translations[$this->locale][$group]))
            $this->loadGroup($group);

        $translation = isset($this->translations[$this->locale][$group][$name]) ? $this->translations[$this->locale][$group][$name] : $key;

        return $placeholders ? strtr($translation, $placeholders) : $translation;
    }

    /*
     * Shortcut for translate method
     */
    public function t($key, $placeholders = [])
    {
        return $this->translate($key, $placeholders);
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        if (!isset($this->translations[$this->locale]))
            $this->translations[$this->locale] = [];
    }

    public function getActiveGroup()
    {
        return $this->active_group;
    }

    public function setActiveGroup($group)
    {
        $this->active_group = $group;
    }

    protected function loadGroup($group)
    {
        if (!isset($this->translations[$this->locale][$group]))
            $this->translations[$this->locale][$group] = [];

        $cache = $this->cache->getItem('i18n/' . $this->locale . '/' . $group);

        if ($cache->isMiss())
        {
            $translations = I18nQuery::create()
                ->filterByGroup($group)
                ->joinWithI18n($this->locale)
                ->find();

            foreach ($translations as $translation)
            {
                $this->translations[$this->locale][$group][$translation->getName()] = $translation->getText();
            }

            $cache->set($this->translations[$this->locale][$group]);
        }
        else
        {
            $this->translations[$this->locale][$group] = $cache->get();
        }
    }

    protected function extractTranslationKey($key)
    {
        $parts = explode('.', $key, 2);

        if (!$parts[0])
            throw new I18nException('Translation group can not be empty. This usually happens when you try to translate empty string or a string starting with dot.');

        return $parts;
    }
}