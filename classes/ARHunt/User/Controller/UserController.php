<?php

namespace ARHunt\User\Controller;

use ARHunt\User\Model\UserTable;
use ARHunt\Hunt\Model\HuntTable;
use ARHunt\Hunt\Model\Hunt;

class UserController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function ajaxUserExists($request, $response, $args)
    {
        $data = $request->getAttribute('__data');
        if (!isset($data['nickname'])) {
            return $response->withStatus(400)
                            ->withJson([ 'status' => 'missing-nickname' ]);
        }
        $nickname = $data['nickname'];
        $userTable = new UserTable($this->container->get('db'));
        try {
            $userTable->getUserByNickname($nickname);
        } catch (\RuntimeException $ex) {
            return $response->withStatus(404)
                            ->withJson([ 'status' => 'not-found' ]);
        }
        return $response->withJson([
            'status' => 'ok',
            'profilePath' => $this->container->get('router')->pathFor('profile', [
                'nickname' => $nickname
            ])
        ]);
    }

    public function profile($request, $response, $args)
    {
        $userTable = new UserTable($this->container->get('db'));
        if (isset($args['nickname'])) {
            try {
                $user = $userTable->getUserByNickname($args['nickname']);
            } catch (\RuntimeException $ex) {
                return $response->withStatus(404);
            }
        } else {
            $user = $_SESSION['user'];
        }
        $huntTable = new HuntTable($this->container->get('db'));
        return $this->container->get('view')->render($response, 'profile.html.twig', [
            'user' => $user,
            'summary' => $huntTable->getHuntsByPlayer($user, [ Hunt::CLOSED, Hunt::CANCELLED ]),
            'open' => $huntTable->getHuntsByAuthor($user, [ Hunt::PUBLISHED, Hunt::CALLING, Hunt::CALLING, Hunt::PREPARING, Hunt::WAITING, Hunt::PLAYING ]),
            'closed' => $huntTable->getHuntsByAuthor($user, [ Hunt::CLOSED, Hunt::CANCELLED ])
        ]);
    }
}