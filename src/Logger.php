<?php namespace articfox1986\phpparticle;

/**
 * Debug logger.
 * PHP 5.6 offers us the ability to use the LoggerTrait, which would
 * save some code duplication. That can be a future enhancement.
 */

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
//use Psr\Log\LoggerTrait;

class Logger implements LoggerInterface
{
    //use LoggerTrait;

    // The debugType is HTML or TEXT.
    private $debugType = 'HTML';

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        // Run through thr context array to find any values to use
        // as substitution variables for the message. Any remaining can be
        // listed at the end.

        foreach($context as $key => $value) {
            $placeholder = '{' . $key . '}';

            if (strpos($message, $placeholder) !== false) {
                $message = str_replace($placeholder, $value, $message);
                unset($context[$key]);
            }
        }

        if ( ! empty($context)) {
            $context_string = print_r($context, true);

            if ($this->debugType === 'HTML') {
                $context_string = '<pre>' . $context_string . '</pre>';
            }

            $context_string .= "\n";
        } else {
            $context_string = '';
        }

        if ($this->debugType === 'HTML') {
            echo '[' . $level . '] ' . $message . $context_string . '<br />' . "\n";
        } else if($this->debugType === 'TEXT') {
            echo '[' . $level . '] ' . $message . "\n" . $context_string;
        }
    }

    /**
     * Set the debug type on construction.
     */
    public function __construct($debugType = 'HTML')
    {
        $this->setDebugType($debugType);
    }

    /**
     * Sets the debug type. Use "HTML" for errors automatically formatted for embedding into a webpage and "TEXT" for unformatted raw errors
     *
     * @param string $debugType The debug type (either "HTML" or "TEXT")
     *
     * @return void
     *
     */
    public function setDebugType($debugType)
    {
        $debugType = strtoupper($debugType);

        if ($debugType === 'HTML' || $debugType === 'TEXT') {
            $this->debugType = $debugType;
        } else {
            throw new Exception(sprintf('Bad debug type "%s"', $debugType));
        }
    }

    /**
     * Gets the debug type
     * @return string
     */
    public function getDebugType()
    {
        return $this->debugType;
    }


    //
    // Everything below here is a replica of the Psr\Log\LoggerTrait
    //

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
