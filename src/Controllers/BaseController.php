<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\Controllers;

use Ilyamur\PhpMvc\Service\Auth;
use Ilyamur\PhpMvc\Service\Flash;
use Gregwar\Captcha\CaptchaBuilder;

/**
 * Base controller
 *
 * PHP version 8.0
 */
abstract class BaseController
{
    /**
     * Parameters from the matched route
     * @var array
     */
    protected array $routeParams = [];

    /**
     * Class constructor
     *
     * @param array $route_params  Parameters from the route
     *
     * @return void
     */
    public function __construct(array $routeParams)
    {
        $this->routeParams = $routeParams;
    }

    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods.
     *
     * @param string $name  Method name
     * @param array $args Arguments passed to the method
     *
     * @return void
     */
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

    /**
     * Before filter - called before an action method.
     *
     * @return void
     */
    protected function before()
    {
    }

    /**
     * After filter - called after an action method.
     *
     * @return void
     */
    protected function after()
    {
    }

    /**
     * Redirect to a different page
     *
     * @param string $url  The relative URL
     *
     * @return void
     */
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

    /**
     * Require the user to be logged in before giving access to the requested page.
     * Remember the requested page for later, then redirect to the login page.
     *
     * @return void
     */
    public function requireLogin(): void
    {
        if (Auth::getUser()) {
            return;
        }
        // Flash::addMessage('Please login to access that page');
        Flash::addMessage('Please log in first', Flash::INFO);
        Auth::rememberRequestedPage();

        $this->redirect('/login');
    }

    /**
     * Redirect to the Root with message
     *
     * @param string $msg  The flash message
     *
     * @return void
     */
    protected function toRootWithWarning(string $msg = 'Nothing found'): void
    {
        Flash::addMessage($msg, Flash::WARNING);
        $this->redirect('/');
    }

    /**
     * Get captcha from Session array
     *
     * @return string or null
     */
    protected function getCaptcha(): ?string
    {
        return $_SESSION['phrase'] ?? null;
    }

    /**
     * Generate captcha by CaptchaBuilder lib
     *
     * @return string
     */
    protected function generateCaptcha(): string
    {
        $captcha = new CaptchaBuilder();
        $captcha->build();
        $_SESSION['phrase'] = $captcha->getPhrase();

        return $captcha->inline();
    }

    /**
     * Checking if user is admin
     *
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return !!Auth::getUser()?->is_admin;
    }
}
