<?php

namespace Academe\Particle\Log;

/**
 * Debug logger to echo the debug messages.
 * This emulates what the original phpParticle package did.
 * Same as EchoLogger, but uses PHP5.6 traits.
 */

use Exception;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;

class Echo56Logger implements LoggerInterface
{
    use LoggerTrait;

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
}
