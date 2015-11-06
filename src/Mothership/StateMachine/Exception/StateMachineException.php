<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mothership
 * @package   Mothership_Exception
 * @author    Maurizio Brioschi <brioschi@mothership.de>
 * @copyright Copyright (c) 2015 Mothership GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.mothership.de/
 */

namespace Mothership\StateMachine\Exception;

use Mothership\Exception\ExceptionAbstract;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class StateMachineException extends ExceptionAbstract
{
    protected $logger;

    public function __construct($message = "", $code = 0, \Exception $previous = null, OutputInterface $output = null,
                                $send_alert = true)
    {
        $this->setLog();
        parent::__construct($message, $code, $previous, $output, $send_alert);
    }

    /**
     * create the log directory and the loggers
     * @throws \Exception
     */
    private function setLog()
    {
        if (!file_exists(getcwd() . '/logs')) {
            if (!mkdir(__DIR__ . '/logs')) {
                throw new \Exception("Failed to create the logs directory!!");
                exit;
            }
        }

        $level = $this->getGravityLevel();
        switch ($level) {
            case 'danger' || 'low_danger':
                $this->logger = new Logger("statemachine_error");
                $this->logger->pushHandler(new StreamHandler(getcwd() . '/logs/statemachine_error.log', Logger::ERROR));
                $this->logger->pushHandler(new FirePHPHandler());
                break;
            case 'warning':
                $this->logger = new Logger("statemachibe_warning");
                $this->logger->pushHandler(new StreamHandler(getcwd() . '/logs/statemachine_warning.log',
                    Logger::WARNING));
                $this->logger->pushHandler(new FirePHPHandler());
                break;
            case 'info':
                $this->logger = new Logger("statemachine_info");
                $this->logger->pushHandler(new StreamHandler(getcwd() . '/logs/statemachine_info.log', Logger::INFO));
                $this->logger->pushHandler(new FirePHPHandler());
                break;
        }
    }

    public function alert()
    {
        parent::alert();
        $this->logger->addInfo($this->message);
    }
}

