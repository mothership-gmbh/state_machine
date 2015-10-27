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

namespace Mothership\Exception;

use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ExceptionAbstract extends Exception
{
    protected $gravity; //score from 0 to 100 where 100 is the most dangerous
    protected $output;

    /**
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @param OutputInterface|null $output if null a default Symfony\Component\Console\Output\ConsoleOutput will be
     * create
     * @param bool|true $send_alert if is true the exception will be write on the $output
     */
    public function __construct($message = "", $code = 0, Exception $previous = null, OutputInterface $output = null,
                                $send_alert = true)
    {
        parent::__construct($message, $code, $previous);

        if ($previous != null) {
            $this->message .= "\n" . $previous->getMessage();
        }

        $this->output = $output;
        if (is_null($output) || !isset($output)) {
            $this->output = new ConsoleOutput();
        }

        $this->gravity = $this->code;

        if ($send_alert && $previous == null) {
            $this->alert();
        }
    }

    /**
     * Get the gravity of the exception
     * @return int
     */
    public function getGravity()
    {
        return $this->gravity;
    }

    /**
     * Get the gravity level of the exception
     * @return string
     */
    protected function getGravityLevel()
    {
        switch ($this->gravity) {
            case $this->gravity > 90:
                return "danger";
            case $this->gravity >= 80 && $this->gravity < 90:
                return "low-danger";
            case $this->gravity >= 50 && $this->gravity < 80:
                return "warning";
            default:
                return "info";
        }
    }

    public function alert()
    {
        $level = $this->getGravityLevel();
        switch ($level) {
            case 'danger':
                $this->output->writeln("<error>DANGER: " . $this->message . "\n\nTHIS IS THE END!!!</error>");
                break;
            case 'low-danger':
                $this->output->writeln("<error>DANGER: " . $this->message . "</error>");
                break;
            case 'waring':
                $this->output->writeln("<comment>WARNING: " . $this->message . "</comment>");
                break;
            case 'info':
                $this->output->writeln("<info>INFO: " . $this->message . "</info>");
                break;
        }

    }
}

