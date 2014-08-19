<?php

namespace Perfumer\Model;

use App\Model\Base\Attachment as BaseAttachment;
use Propel\Runtime\Connection\ConnectionInterface;

class Attachment extends BaseAttachment
{
    const PURPOSE_COMMON = 0;

    public function postDelete(ConnectionInterface $con = null)
    {
        @unlink(ATTACHMENTS_DIR . $this->getPath());
    }
}