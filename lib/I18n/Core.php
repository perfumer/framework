<?php

namespace Perfumer\I18n;

use App\Model\I18nQuery;
use Perfumer\Cache\AbstractCache;
use Perfumer\I18n\Exception\I18nException;

class Core
{
    protected $cache;

    protected $translations = [];
    protected $locale;
    protected $active_group;

    public function __construct(AbstractCache $cache)
    {
        $this->cache = $cache;
    }

    public function translate($key)
    {
        if ($this->locale === null)
            throw new I18nException('Translation locale is not defined.');

        list($group, $name) = $this->extractTranslationKey($key);

        if (!isset($this->translations[$this->locale][$group]))
            $this->loadGroup($group);

        return isset($this->translations[$this->locale][$group][$name]) ? $this->translations[$this->locale][$group][$name] : $key;
    }

    public function t($name)
    {
        if ($this->locale === null)
            throw new I18nException('Translation locale is not defined.');

        if ($this->active_group === null)
            throw new I18nException('Active group not set for using short syntax.');

        if (!isset($this->translations[$this->locale][$this->active_group]))
            $this->loadGroup($this->active_group);

        return isset($this->translations[$this->locale][$this->active_group][$name]) ? $this->translations[$this->locale][$this->active_group][$name] : $name;
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

        if (!$translations = $this->cache->get('i18n.' . $this->locale . '.' . $group))
        {
            $translations = I18nQuery::create()
                ->filterByGroup($group)
                ->join('I18n')
                ->withColumn('I18n.translation', 'translation')
                ->useI18nQuery()
                    ->filterByLocale($this->locale)
                ->endUse()
                ->find();

            foreach ($translations as $translation)
            {
                $this->translations[$this->locale][$group][$translation->getName()] = $translation->getTranslation();
            }

            $this->cache->set('i18n.' . $this->locale . '.' . $group, serialize($this->translations[$this->locale][$group]));
        }
        else
        {
            $this->translations[$this->locale][$group] = unserialize($translations);
        }
    }

    protected function extractTranslationKey($key)
    {
        $parts = explode('.', $key, 2);

        if (!$parts[0])
            throw new I18nException('Translation group can not be empty.');

        if ($parts[1] === null)
            throw new I18nException('Translation name can not be null.');

        return $parts;
    }
}