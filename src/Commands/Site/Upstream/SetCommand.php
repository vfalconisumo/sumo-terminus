<?php

namespace Pantheon\Terminus\Commands\Site\Upstream;

use Pantheon\Terminus\Commands\Site\SiteCommand;
use Pantheon\Terminus\Commands\WorkflowProcessingTrait;
use Pantheon\Terminus\Exceptions\TerminusException;

/**
 * Class SetCommand
 * @package Pantheon\Terminus\Commands\Site
 */
class SetCommand extends SiteCommand
{
    use WorkflowProcessingTrait;

    /**
     * Changes a site upstream.
     *
     * @authorize
     * @interact
     *
     * @command site:upstream:set
     *
     * @param string $site_name Site name
     * @param string $upstream_id Upstream name or UUID
     *
     * @usage <site> <upstream_id> Updates <site>'s upstream to <upstream_id>.
     */
    public function set($site_name, $upstream_id)
    {
        $site = $this->getSiteById($site_name);
        if (!$site->getAuthorizations()->can('switch_upstream')) {
            throw new TerminusException('You do not have permission to change the upstream of this site.');
        }

        $upstream = $this->session()->getUser()->getUpstreams()->get($upstream_id);
        $msg_params = ['site' => $site->getName(), 'upstream' => $upstream->get('label'),];

        if (!$this->confirm('Are you sure you want change the upstream for {site} to {upstream}?', $msg_params)) {
            return;
        }
        $previous_upstream_id = $site->getUpstream()->id;
        if ($previous_upstream_id) {
            $this->log()->info(
                'To undo this change run `terminus site:upstream:set {site} {upstream}`',
                ['site' => $site->id, 'upstream' => $previous_upstream_id,]
            );
        }

        $this->processWorkflow($site->setUpstream($upstream->id));
        $this->log()->notice('Set upstream for {site} to {upstream}', $msg_params);
    }
}
