<?php

namespace Pantheon\Terminus\Commands\Backup\Automatic;

use Consolidation\OutputFormatters\StructuredData\PropertyList;
use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;

/**
 * Class GetCommand
 * @package Pantheon\Terminus\Commands\Backup\Automatic
 */
class InfoCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;

    /**
     * Displays the hour when daily backups are created and the day of the week when weekly backups are created.
     *
     * @authorize
     * @interact
     *
     * @command backup:automatic:info
     *
     * @field-labels
     *     daily_backup_hour: Daily Backup Hour
     *     weekly_backup_day: Weekly Backup Day
     *     expiry: Weekly Backup Expiry
     * @default-string-field weekly_backup_day
     * @param string $site_env Site & environment in the format `site-name.env`
     *
     * @usage <site>.<env> Displays the day when <site>'s <env> environment's weekly backup is created.
     * @usage <site>.<env> --format=table Displays the hour of <site>'s <env> environment's daily backups (retained for one week), day on which its weekly backups (retained for one month) are made, and how long it is kept for.
     *
     * @return PropertyList
     *
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     */
    public function getSchedule($site_env)
    {
        $schedule = $this->getEnv($site_env)->getBackups()->getBackupSchedule();
        if (is_null($schedule['daily_backup_hour'])) {
            $this->log()->notice('Backups are not currently scheduled to be run.');
        }
        return new PropertyList($schedule);
    }
}
