<?php

namespace Pantheon\Terminus\Commands\Env;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Commands\StructuredListTrait;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;

/**
 * Class ListCommand
 * @package Pantheon\Terminus\Commands\Env
 */
class ListCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;
    use StructuredListTrait;

    /**
     * Displays a list of the site environments.
     *
     * @authorize
     * @filter-output
     * @interact
     *
     * @command env:list
     * @aliases envs
     *
     * @field-labels
     *     id: ID
     *     created: Created
     *     domain: Domain
     *     connection_mode: Connection Mode
     *     locked: Locked
     *     initialized: Initialized
     * @return RowsOfFields
     *
     * @param string $site_id Site name
     *
     * @usage <site> Displays a list of <site>'s environments.
     */
    public function listEnvs($site_id)
    {
        $site = $this->getSiteById($site_id);
        if ($site->isFrozen()) {
            $this->log()->warning('This site is frozen. Its test and live environments are unavailable.');
        }
        return $this->getRowsOfFields($site->getEnvironments());
    }
}
