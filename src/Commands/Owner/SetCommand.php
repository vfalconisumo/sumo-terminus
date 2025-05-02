<?php

namespace Pantheon\Terminus\Commands\Owner;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Commands\WorkflowProcessingTrait;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Exceptions\TerminusNotFoundException;

/**
 * Class SetCommand
 * @package Pantheon\Terminus\Commands\Owner
 */
class SetCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;
    use WorkflowProcessingTrait;

    /**
     * Change the owner of a site
     *
     * @authorize
     * @interact
     *
     * @command owner:set
     *
     * @param string $site_name The name or UUID of a site to assign a new owner to
     * @param string $owner The UUID, email, or full name of the user to set as the site's new owner
     *
     * @usage <site> <new_owner> Promotes <new_owner> to be the owner of <site>.
     */
    public function setOwner($site_name, $owner)
    {
        $site = $this->sites->get($site_name);
        try {
            $user = $site->getUserMemberships()->get($owner)->getUser();
        } catch (TerminusNotFoundException $e) {
            throw new TerminusNotFoundException(
                'The new owner must be added with "terminus site:team:add" before promoting.'
            );
        }
        $this->processWorkflow($site->setOwner($user->id));
        $this->log()->notice(
            'Promoted {user} to owner of {site}',
            ['user' => $owner, 'site' => $site->getName(),]
        );
    }
}
