<?php

namespace Pantheon\Terminus\Commands\Backup;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Pantheon\Terminus\Commands\StructuredListTrait;

/**
 * Class ListCommand.
 *
 * @package Pantheon\Terminus\Commands\Backup
 */
class ListCommand extends BackupCommand
{
    use StructuredListTrait;

    /**
     * Lists backups for a specific site and environment.
     *
     * @authorize
     * @filter-output
     *
     * @command backup:list
     * @aliases backups
     *
     * @field-labels
     *     file: Filename
     *     size: Size
     *     date: Date
     *     expiry: Expiry
     *     initiator: Initiator
     * @param string $site_env Site & environment in the format `site-name.env`
     * @param array $options
     * @return RowsOfFields
     *
     * @option string $element [all|code|files|database|db] Backup element filter
     *
     * @usage <site>.<env> Lists all backups made of <site>'s <env> environment.
     * @usage <site>.<env> --element=<element> Lists all <element> backups made of <site>'s <env> environment.
     *
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     */
    public function listBackups($site_env, array $options = ['element' => 'all',])
    {
        $env = $this->getEnv($site_env);

        $backups = $env->getBackups()->filterForFinished();
        $element = $this->getElement($options['element']);
        if ($element !== null) {
            $backups->filterForElement($element);
        }
        return $this->getRowsOfFields($backups);
    }
}
