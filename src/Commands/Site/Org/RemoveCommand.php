<?php

namespace Pantheon\Terminus\Commands\Site\Org;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Commands\WorkflowProcessingTrait;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Exceptions\TerminusException;

/**
 * Class RemoveCommand
 * @package Pantheon\Terminus\Commands\Site\Org
 */
class RemoveCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;
    use WorkflowProcessingTrait;

    /**
     * Disassociates a supporting organization from a site.
     *
     * @authorize
     * @interact
     *
     * @command site:org:remove
     * @aliases site:org:rm
     *
     * @param string $site Site name
     * @param string $organization Organization name or UUID
     *
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     * @throws \Pantheon\Terminus\Exceptions\TerminusNotFoundException
     *
     * @usage <site> <organization> Disassociates <organization> as a supporting organization from <site>.
     */
    public function remove($site, $organization)
    {
        $site = $this->getSiteById($site);
        $membership = $site->getOrganizationMemberships()->get($organization);

        $workflow = $membership->delete();
        $this->log()->notice(
            'Removing {org} as a supporting organization from {site}.',
            ['site' => $site->getName(), 'org' => $membership->getOrganization()->getName()]
        );
        $this->processWorkflow($workflow);
        $this->log()->notice($workflow->getMessage());
    }
}
