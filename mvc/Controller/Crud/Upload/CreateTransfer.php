<?php

namespace Perfumer\MVC\Controller\Crud\Upload;

use App\Model\File;
use App\Model\FileQuery;
use Perfumer\Helper\Text;
use Perfumer\MVC\Controller\Exception\CrudException;
use Propel\Runtime\Map\TableMap;
use Upload\File as FileUpload;
use Upload\Storage\FileSystem;

trait CreateTransfer
{
    protected function getModelName()
    {
        return null;
    }

    protected function postPermission()
    {
    }

    protected function postValidate(FileUpload $file)
    {
    }

    protected function postPrePersist(File $attachment)
    {
    }

    protected function postPreSave(File $attachment)
    {
    }

    protected function postAfterSuccess(File $attachment)
    {
    }

    protected function postSetContent(File $attachment)
    {
    }

    public function post()
    {
        $this->postPermission();

        $digest = $this->newDigest();
        $splitted_digest = implode('/', str_split($digest, 2)) . '/';
        $target_folder = date('Y/m/d/H/i/');

        $storage = new FileSystem(FILES_TMP_DIR . $splitted_digest);
        $file = new FileUpload('file', $storage);

        $this->postValidate($file);

        // This is temporary solution for UTF-8 filename bug
        $name = explode('.', $_FILES['file']['name']);
        $name = reset($name);

        $mime = $file->getMimetype();
        $size = $file->getSize();

        if (!$model_name = $this->getModelName())
            throw new CrudException('Model name for upload action is not defined');

        $attachment = new File();
        $attachment->setModelName($model_name);

        try
        {
            $file->upload();

            $this->postPrePersist($attachment);

            $attachment->setName($name);
            $attachment->setExtension($file->getExtension());
            $attachment->setDigest($digest);
            $attachment->setContentType($mime);
            $attachment->setSize($size);
            $attachment->setPath($target_folder . $digest . '.' . $file->getExtension());

            if ($this->getUser()->isLogged())
                $attachment->setCreator($this->getUser());

            if (isset($_POST['model_id']))
                $attachment->setModelId((int) $_POST['model_id']);

            $this->postPreSave($attachment);

            if ($attachment->save())
            {
                @unlink(FILES_DIR . $attachment->getPath());
                @mkdir(FILES_DIR . $target_folder, 0777, true);
                @rename(FILES_TMP_DIR . $splitted_digest . $file->getNameWithExtension(), FILES_DIR . $attachment->getPath());

                $this->postAfterSuccess($attachment);
                $this->postSetContent($attachment);
            }
        }
        catch (\Exception $e)
        {
            $this->addErrors($file->getErrors());
        }
    }

    protected function newDigest()
    {
        do
        {
            $digest = Text::generateString();

            $count = FileQuery::create()
                ->filterByDigest($digest)
                ->count();
        }
        while ($count > 0);

        return $digest;
    }
}