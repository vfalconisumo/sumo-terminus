<?php

namespace Pantheon\Terminus\Commands\Redis;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Commands\WorkflowProcessingTrait;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;

/**
 * Class EnableCommand
 * @package Pantheon\Terminus\Commands\Redis
 */
class EnableCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;
    use WorkflowProcessingTrait;

    /**
     * Enables Redis add-on for a site.
     *
     * @authorize
     * @interact
     *
     * @command redis:enable
     *
     * @param string $site_id Site name
     *
     * @usage <site> Enables Redis add-on for <site>.
     */
    public function enable($site_id)
    {
        $site = $this->getSiteById($site_id);
        $workflow = $site->getRedis()->enable();
        $this->processWorkflow($workflow);
        $this->log()->notice($workflow->getMessage());
    }
}
