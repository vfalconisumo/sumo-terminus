<?php

namespace Pantheon\Terminus\Commands\Workflow;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Commands\StructuredListTrait;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;

/**
 * Class ListCommand
 * @package Pantheon\Terminus\Commands\Workflow
 */
class ListCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;
    use StructuredListTrait;

    /**
     * Displays the list of the workflows for a site.
     *
     * @authorize
     * @interact
     *
     * @command workflow:list
     * @aliases workflows
     *
     * @option bool $all Return all of the available workflows, not just the most recent 100
     *
     * @field-labels
     *     id: Workflow ID
     *     env: Environment
     *     workflow: Workflow
     *     user: User
     *     status: Status
     *     started_at: Started At
     *     finished_at: Finished At
     *     time: Time Elapsed
     * @return RowsOfFields
     *
     * @param string $site_id Site name
     *
     * @usage <site> Displays the list of the workflows for <site>.
     */
    public function wfList($site_id, $options = [
        'all' => false,
    ])
    {
        $paging = (bool) $options['all'];
        $site = $this->getSiteById($site_id);
        return $this->getRowsOfFields(
            $site->getWorkflows()->setPaging($paging)->fetch(),
            [
                'message' => 'No workflows have been run on {site}.',
                'message_options' => ['site' => $site->getName()],
            ]
        );
    }
}
