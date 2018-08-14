<?php


namespace NxsSpryker\Service\Sentry\Business\Model\Handler;


use NxsSpryker\Service\NxsErrorHandler\Dependency\Plugin\NxsErrorHandlerPlugin;
use NxsSpryker\Service\NxsErrorHandler\Dependency\Plugin\NxsExceptionHandlerPlugin;
use Spryker\Service\Kernel\AbstractPlugin;

/**
 * @method \NxsSpryker\Service\Sentry\SentryServiceFactory getFactory()
 */
class ExceptionHandler extends AbstractPlugin implements NxsExceptionHandlerPlugin
{
    /**
     * @var mixed
     */
    private $oldExceptionHandler;

    /**
     * @param bool $isDebug
     */
    public function register(bool $isDebug): void
    {
        if ($isDebug) {
            $this->oldExceptionHandler = set_exception_handler(
                [
                    $this,
                    'handleException'
                ]
            );
        }
    }

    /**
     * @param \Throwable $throwable
     */
    public function handleException(\Throwable $throwable): void
    {
        $this->getFactory()->getSentryClient()->captureException($throwable);

        if ($this->oldExceptionHandler) {
            \call_user_func(
                $this->oldExceptionHandler,
                $throwable
            );
        }
    }

}