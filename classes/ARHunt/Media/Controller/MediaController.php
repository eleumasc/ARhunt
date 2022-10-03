<?php

namespace ARHunt\Media\Controller;

use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Hunt\Model\Hunt;
use ARHunt\Media\Model\MediaTable;
use ARHunt\Media\Model\Media;
use ARHunt\Media\Form\MediaForm;

class MediaController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function ajaxEdit($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        $form = new MediaForm;
        if (!$form->setData($data)) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => $form->getError() ]);
        }
        $mediaTable = new MediaTable($this->container->get('db'));
        try {
            $media = $mediaTable->getMediaById($form->getId());
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $huntTable = new HuntTable($this->container->get('db'));
        $hunt = $huntTable->getHuntById($media->hunt);
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        $media->name = $form->getName();
        $mediaTable->saveMedia($media);
        return $response->withJson([ 'status' => 'ok' ]);
    }

    public function ajaxDelete($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        if (!isset($data['id'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-id' ]);
        }
        $mediaTable = new MediaTable($this->container->get('db'));
        try {
            $media = $mediaTable->getMediaById($data['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        $huntTable = new HuntTable($this->container->get('db'));
        $hunt = $huntTable->getHuntById($media->hunt);
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        $mediaTable->deleteMedia($media);
        $this->container->get('storage')->deleteFile($media->hunt, "{$media->id}.{$media->getFileExtension()}");
        return $response->withJson([ 'status' => 'ok' ]);
    }

    public function listMedia($request, $response, $args)
    {
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($args['hunt']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403);
        }
        $mediaTable = new MediaTable($this->container->get('db'));
        $media = $mediaTable->getMediaByHunt($hunt);
        return $this->container->get('view')->render($response, 'media-list.html.twig', [
            'hunt' => $hunt,
            'media' => $media,
            'usedSpace' => $mediaTable->getUsedSpace($hunt),
            'maxSpace' => $this->container->get('settings')['storage']['max_space']
        ]);
    }

    public function upload($request, $response, $args)
    {
        $data = $request->getParsedBody();
        if (!isset($data['hunt'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-hunt' ]);
        }
        $huntTable = new HuntTable($this->container->get('db'));
        try {
            $hunt = $huntTable->getHuntById($data['hunt']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        if ($hunt->author !== $_SESSION['user']->nickname) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        if ($hunt->status != Hunt::EDITING && $hunt->status != Hunt::PUBLISHED) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'you-shall-not-pass' ]);
        }
        $uploadedFile = $request->getUploadedFiles()['file'];
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return $response->withStatus(500)
                            ->withJson([ 'status' => 'upload-error' ]);
        }
        $mediaTable = new MediaTable($this->container->get('db'));
        if ($mediaTable->getUsedSpace($hunt) + $uploadedFile->getSize() > $this->container->get('settings')['storage']['max_space']) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'no-more-space' ]);
        }
        $media = new Media;
        $media->hunt = $hunt->id;
        $media->name = substr($uploadedFile->getClientFilename(), 0, 120);
        $media->filename = $uploadedFile->getClientFilename();
        $mediaType = $uploadedFile->getClientMediaType();
        $slashIndex = strpos($mediaType, '/');
        $media->type = substr($mediaType, 0, $slashIndex);
        $media->subtype = substr($mediaType, $slashIndex + 1);
        $media->length = $uploadedFile->getSize();
        if (!$media->getFileExtension()) {
            return $response->withStatus(403)
                            ->withJson([ 'status' => 'invalid-type' ]);
        }
        $mediaTable->saveMedia($media);
        $uploadedFile->moveTo($this->container->get('storage')->getFilePath($media->hunt, "{$media->id}.{$media->getFileExtension()}"));
        return $response->withStatus(301)
                        ->withHeader('Location', $this->container->get('router')->pathFor('media-list', [
                            'hunt' => $media->hunt
                        ]))
                        ->withJson([ 'status' => 'ok' ]);
    }

    public function download($request, $response, $args)
    {
        $mediaTable = new MediaTable($this->container->get('db'));
        try {
            $media = $mediaTable->getMediaById($args['id']);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404);
        }
        $settings = $this->container->get('settings');
        return $response->withStatus(301)
                        ->withHeader('Location', $settings['arhunt']['path'] . $settings['storage']['path'] . "/{$media->hunt}/{$media->id}.{$media->getFileExtension()}");
    }
}