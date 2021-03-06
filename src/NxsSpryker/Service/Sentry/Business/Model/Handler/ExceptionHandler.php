<?php


namespace NxsSpryker\Service\Sentry\Business\Model\Handler;


use NxsSpryker\Service\NxsErrorHandler\Dependency\Plugin\NxsErrorHandlerPlugin;
use NxsSpryker\Service\NxsErrorHandler\Dependency\Plugin\NxsExceptionHandlerPlugin;
use Spryker\Service\Kernel\AbstractPlugin;

/**
 * @method \NxsSpryker\Service\Sentry\SentryServiceFactory getFactory()
 * @method \NxsSpryker\Service\Sentry\SentryService getService()
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
        if (
            $throwable instanceof \ErrorException
            && ($throwable->getSeverity() & $this->getConfig()->getIgnoredErrorTypes()) === 0
        ) {
            return;
        }

        $this->getService()->captureException(
            $throwable,
            [
                'tags' =>
                    [
                        'handler' => __CLASS__
                    ]
            ]
        );

        if ($this->oldExceptionHandler && $this->getConfig()->isRunPreviousHandler()) {
            \call_user_func(
                $this->oldExceptionHandler,
                $throwable
            );
        }
    }

}