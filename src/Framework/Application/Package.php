<?php

namespace Perfumer\Framework\Application;

interface Package
{
    public function configure(Application $application): void;
}
