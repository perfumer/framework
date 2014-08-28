<?php

namespace Perfumer\Controller\Crud\Upload;

use App\Model\Attachment;
use App\Model\AttachmentQuery;
use Propel\Runtime\Map\TableMap;
use Upload\File;
use Upload\Storage\FileSystem;

trait CreateTransfer
{
    protected function postPermission()
    {
    }

    protected function postValidate(File $file)
    {
    }

    protected function postPrePersist(Attachment $attachment)
    {
    }

    protected function postPreSave(Attachment $attachment)
    {
    }

    protected function postAfterSuccess(Attachment $attachment)
    {
    }

    public function post()
    {
        $this->postPermission();

        $storage = new FileSystem(ATTACHMENTS_DIR);
        $file = new File('file', $storage);

        $this->postValidate($file);

        // This is temporary solution for UTF-8 filename bug
        $name = explode('.', $_FILES['file']['name']);
        $name = reset($name);

        $mime = $file->getMimetype();
        $size = $file->getSize();

        try
        {
            $file->upload();

            $digest = $this->newDigest($file->getName());
            $path = $this->digestPath($digest);

            $attachment = new Attachment();

            $this->postPrePersist($attachment);

            $attachment->setName($name);
            $attachment->setExtension($file->getExtension());
            $attachment->setDigest($digest);
            $attachment->setContentType($mime);
            $attachment->setSize($size);
            $attachment->setPath($path[0] . '.' . $file->getExtension());

            if ($this->getUser()->isLogged())
                $attachment->setUser($this->getUser());

            if (isset($_POST['model_id']))
                $attachment->setModelId((int) $_POST['model_id']);

            $this->postPreSave($attachment);

            if ($attachment->save())
            {
                @unlink(ATTACHMENTS_DIR . $attachment->getPath());
                @mkdir(ATTACHMENTS_DIR . $path[1], 0777, true);
                @rename(ATTACHMENTS_DIR . $file->getNameWithExtension(), ATTACHMENTS_DIR . $attachment->getPath());

                $this->setContent($attachment->toArray(TableMap::TYPE_FIELDNAME));

                $this->postAfterSuccess($attachment);
            }
        }
        catch (\Exception $e)
        {
            $this->addErrors($file->getErrors());
        }
    }

    protected function newDigest($name)
    {
        $digest = $name . '_' . uniqid(time() . '_', true);

        do
        {
            $digest = md5($digest);

            $count = AttachmentQuery::create()
                ->filterByDigest($digest)
                ->count();
        }
        while ($count > 0);

        return $digest;
    }

    protected function digestPath($digest)
    {
        $first = substr($digest, 0, 6);
        $last = substr($digest, 6);

        $folder = implode('/', str_split($first, 2));

        return [$folder . '/' . $last, $folder];
    }
}