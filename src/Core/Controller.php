<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Core;

use Ilyamur\PhpMvc\App\Auth;
use Ilyamur\PhpMvc\App\Flash;

abstract class Controller
{
    protected array $routeParams = [];

    public function __call(string $methodName, array $args): void
    {
        $methodName = $methodName . 'Action';

        if (!method_exists($this, $methodName)) {
            throw new \Exception("Method $methodName not found in controller" . get_class($this));
        }

        if ($this->before() !== false) {
            call_user_func_array([$this, $methodName], $args);
            $this->after();
        }
    }

    public function __construct(array $routeParams)
    {
        $this->routeParams = $routeParams;
    }

    protected function before()
    {
    }
    protected function after()
    {
    }

    protected function redirect(string $url): void
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }
        header("location: $protocol://" . $_SERVER['HTTP_HOST'] . $url, true, 303);
        exit;
    }

    public function requireLogin(): void
    {
        if (Auth::getUser()) {
            return;
        }

        Flash::addMessage('Please log in first', Flash::INFO);
        Auth::rememberRequestedPage();
        $this->redirect('/login');
    }

    protected function toRootWithWarning(string $msg = 'Nothing found')
    {
        Flash::addMessage($msg, Flash::WARNING);
        $this->redirect('/');
    }
}
