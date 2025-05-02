<?php

namespace Pantheon\Terminus\Commands\Env;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;

/**
 * Class DiffStatCommand.
 *
 * @package Pantheon\Terminus\Commands\Env
 */
class DiffStatCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;

    /**
     * Displays the diff of uncommitted code changes on a development environment.
     *
     * @interact
     * @authorize
     *
     * @command env:diffstat
     *
     * @param string $site_env Site & development environment in the format `site-name.env`
     *
     * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
     *
     * @field-labels
     *     file: File
     *     status: Status
     *     deletions: Deletions
     *     additions: Additions
     * @usage <site>.<env> Displays a diff of uncommitted code changes on <site>'s <env> environment.
     *
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     */
    public function diffstat($site_env)
    {
        $this->requireSiteIsNotFrozen($site_env);
        $env = $this->getEnv($site_env);

        $diff = (array)$env->diffstat();
        $data = [];
        if (empty($diff)) {
            $this->log()->notice('No changes on server.');
        } else {
            foreach ($diff as $file => $stats) {
                $data[] = array_merge(compact('file'), (array)$stats);
            }
        }
        return new RowsOfFields($data);
    }
}
