<?php

namespace Pantheon\Terminus\Update;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Pantheon\Terminus\Config\ConfigAwareTrait;
use Pantheon\Terminus\DataStore\DataStoreAwareInterface;
use Pantheon\Terminus\DataStore\DataStoreAwareTrait;
use Pantheon\Terminus\DataStore\DataStoreInterface;
use Pantheon\Terminus\Exceptions\TerminusNotFoundException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;

/**
 * Class UpdateChecker
 *
 * @package Pantheon\Terminus\Update
 */
class UpdateChecker implements
    ConfigAwareInterface,
    ContainerAwareInterface,
    DataStoreAwareInterface,
    LoggerAwareInterface
{
    use ConfigAwareTrait;
    use ContainerAwareTrait;
    use DataStoreAwareTrait;
    use LoggerAwareTrait;

    public const DEFAULT_COLOR = "\e[0m";

    public const UPDATE_COMMAND = 'You can update Terminus by running `terminus '
        . 'self:update` or using Homebrew.' . PHP_EOL;

    public const UPDATE_COMMAND_PHAR = 'You can update Terminus by running:' . PHP_EOL . 'terminus self:update';

    public const UPDATE_NOTICE = 'A new Terminus version v{latest_version} is available.'
        . PHP_EOL
        . 'You are currently using version v{running_version}.'
        . PHP_EOL
        . '{update_command}';

    public const UPDATE_NOTICE_COLOR = "\e[38;5;33m";

    public const UPDATE_VARS_COLOR = "\e[38;5;45m";

    /**
     * File to store the last notification time
     */
    public const LAST_NOTIFICATION_FILE = 'last_update_notification';

    /**
     * Notification frequency in seconds (24 hours)
     */
    public const NOTIFICATION_FREQUENCY = 86400;

    /**
     * @var boolean
     */
    private $should_check_for_updates;

    /**
     * @param DataStoreInterface $data_store
     */
    public function __construct(DataStoreInterface $data_store)
    {
        $this->setDataStore($data_store);
    }

    public function run()
    {
        if (!$this->shouldCheckForUpdates()) {
            return;
        }
        $running_version = $this->getRunningVersion();
        try {
            $nickname = \uniqid(__CLASS__ . "-");
            $this->getContainer()
                ->add($nickname, LatestRelease::class)
                ->addArgument($this->getDataStore());
            $version_tester = $this->getContainer()->get($nickname);
            $latest_version = $version_tester->get('version');
        } catch (TerminusNotFoundException $e) {
            $this->logger->debug('Terminus has no saved release information.');
            return;
        }

        $update_exists = version_compare(
            $latest_version,
            $running_version,
            '>'
        );
        $should_hide_update = (bool)$this->getConfig()->get(
            'hide_update_message'
        );
        if ($update_exists && !$should_hide_update) {
            if ($this->shouldShowNotification()) {
                $this->logger->notice($this->getUpdateNotice(), [
                    'latest_version' => self::UPDATE_VARS_COLOR . $latest_version,
                    'running_version' => self::UPDATE_VARS_COLOR . $running_version,
                    'update_command' => self::UPDATE_VARS_COLOR . (
                        \Phar::running()
                            ? self::UPDATE_COMMAND_PHAR
                            : self::UPDATE_COMMAND
                    ),
                ]);
                $this->updateLastNotificationTime();
            }
        }
    }

    /**
     * Stores information on whether or not Terminus should check for updates
     *
     * @param boolean $status True to check for updates
     */
    public function setCheckForUpdates($status)
    {
        $this->should_check_for_updates = $status;
    }

    /**
     * Avoid running the update checker in instances where the output might
     * interfere with scripts.
     */
    private function shouldCheckForUpdates()
    {
        if (empty($this->should_check_for_updates)) {
            if (!function_exists('posix_isatty')) {
                $this->setCheckForUpdates(true);
            } else {
                $this->setCheckForUpdates(
                    posix_isatty(STDOUT) && posix_isatty(STDIN)
                );
            }
        }
        return $this->should_check_for_updates;
    }

    /**
     * Retrieves the version number of the running Terminus instance
     *
     * @return string
     */
    private function getRunningVersion()
    {
        return $this->getConfig()->get('version');
    }

    /**
     * Returns a colorized update notice
     *
     * @return string
     */
    private function getUpdateNotice()
    {
        return self::UPDATE_NOTICE_COLOR
            . str_replace(
                '}',
                '}' . self::UPDATE_NOTICE_COLOR,
                self::UPDATE_NOTICE
            )
            . self::DEFAULT_COLOR;
    }

    /**
     * Determines if it's time to show a notification based on the time elapsed since last notification
     *
     * @return boolean
     */
    private function shouldShowNotification()
    {
        try {
            $last_notification = $this->getDataStore()->get(self::LAST_NOTIFICATION_FILE);
            if (empty($last_notification->time)) {
                // If we have no time, show the notification
                return true;
            }
            $current_time = time();
            return ($current_time - $last_notification->time) > self::NOTIFICATION_FREQUENCY;
        } catch (TerminusNotFoundException $e) {
            // If we've never shown a notification before, show it now
            return true;
        }
    }

    /**
     * Updates the timestamp of the last notification
     */
    private function updateLastNotificationTime()
    {
        $notification_data = (object)[
            'time' => time()
        ];
        $this->getDataStore()->set(self::LAST_NOTIFICATION_FILE, $notification_data);
    }
}
