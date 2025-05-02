<?php

namespace Pantheon\Terminus\Commands\Site\Team;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Commands\WorkflowProcessingTrait;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Exceptions\TerminusException;

/**
 * Class RoleCommand
 * @package Pantheon\Terminus\Commands\Site\Team
 */
class RoleCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;
    use WorkflowProcessingTrait;

    /**
     * Updates a user role on a site team.
     *
     * @authorize
     * @interact
     *
     * @command site:team:role
     *
     * @param string $site_id Site name
     * @param string $member Email, UUID, or full name
     * @param string $role [developer|team_member] Role
     *
     * @usage <site> <user> <role> Updates <user> to be a <role> on <site>'s team.
     */
    public function role($site_id, $member, $role)
    {
        $site = $this->getSiteById($site_id);
        $workflow = $site->getUserMemberships()->get($member)->setRole($role);
        $this->processWorkflow($workflow);
        $this->log()->notice($workflow->getMessage());
    }
}
