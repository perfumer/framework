<?php

namespace Perfumer\MVC\Model;

use App\Model\Base\File as BaseFile;
use Propel\Runtime\Connection\ConnectionInterface;

class File extends BaseFile
{
    const MOD_EMPTY = 0;

    public function postDelete(ConnectionInterface $con = null)
    {
        @unlink(FILES_DIR . $this->getPath());
    }
}