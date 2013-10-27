<?php
namespace CrudFactory\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Response\Stream;
use Zend\Http\Headers;

class Assets extends AbstractActionController
{
    public function renderAction()
    {
        $type = $this->params()->fromRoute('type');
        $file = $this->params()->fromRoute('file');

        $targetAsset = $this->getTargetPath($file, $type);

        if (file_exists($targetAsset)) {
            return $this->stream($targetAsset, $type);
        } else {
            $this->getResponse()->setStatusCode(404);
            return;
        }

    }

    public function stream($file, $type)
    {
        switch ($type) {
            case "css":
                $mimeType = 'text/css';
                break;
            case 'js':
                $mimeType = 'text/javascript';
                break;
            default:
                $mimeType = mime_content_type($file);
        }

        $response = new Stream();
        $response->setStream(fopen($file, 'r'));
        $response->setStatusCode(200);
        $response->setStreamName(basename($file));

        $headers = new Headers();
        $headers->addHeaders(array(
            'Content-Type' => $mimeType,
            'Content-Length' => filesize($file)
        ));
        $response->setHeaders($headers);
        return $response;
    }

    public function getTargetPath($file, $type)
    {
        $config = $this->getServiceLocator()->get('config');
        $assetFolder = $config['zf-hipsters']['zfh-crud-factory']['assetFolder'];

        return CF_MODULE . DS . $assetFolder . DS . $type . DS . $file;

    }
}