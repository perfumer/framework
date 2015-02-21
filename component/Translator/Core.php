<?php

namespace Perfumer\Component\Translator;

use App\Model\TranslationQuery;
use Perfumer\Component\Translator\Exception\TranslatorException;
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
            throw new TranslatorException('Translation locale is not defined.');

        list($group, $name) = $this->extractTranslationKey($key);

        if ($name === null && $this->active_group === null)
            throw new TranslatorException('Active group is not set for using short syntax or you have forgotten to specify name of translation phrase.');

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

        $cache = $this->cache->getItem(['_translator', $this->locale, $group]);

        if ($cache->isMiss())
        {
            $translations = TranslationQuery::create()
                ->filterByGroup($group)
                ->joinWithI18n($this->locale)
                ->find();

            foreach ($translations as $translation)
            {
                $this->translations[$this->locale][$group][$translation->getName()] = $translation->getText();
            }

            $cache->set($this->translations[$this->locale][$group], 3600);
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
            throw new TranslatorException('Translation group can not be empty. This usually happens when you try to translate empty string or a string starting with dot.');

        return $parts;
    }
}