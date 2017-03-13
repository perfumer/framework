<?php

namespace Perfumer\Component\Auth\DataProvider;

abstract class AbstractProvider
{
    /**
     * @param string $token
     * @return string|null
     */
    abstract public function getData(string $token);

    /**
     * @param string $data
     * @return array
     */
    abstract public function getTokens(string $data): array;

    /**
     * @param string $token
     * @param string $data
     * @return bool
     */
    abstract public function saveData(string $token, string $data): bool;

    /**
     * @param string $token
     * @return bool
     */
    abstract public function deleteToken(string $token): bool;
}