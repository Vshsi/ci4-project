<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\MediaModel;

class Media extends BaseController
{
    public function view($filename)
    {
        $potentialPaths = [
            FCPATH . 'uploads/profiles/' . $filename,
            FCPATH . 'uploads/tickets/' . $filename,
        ];

        $filePath = null;
        foreach ($potentialPaths as $path) {
            if (file_exists($path)) {
                $filePath = $path;
                break;
            }
        }

        if (!$filePath) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        $mimeType = mime_content_type($filePath);
        $content  = file_get_contents($filePath);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', filesize($filePath))
            ->setHeader('Cache-Control', 'public, max-age=31536000') 
            ->setBody($content);
    }
}
