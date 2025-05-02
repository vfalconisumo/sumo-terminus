<?php

namespace Pantheon\Terminus\Commands\Lock;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Commands\WorkflowProcessingTrait;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;

/**
 * Class DisableCommand.
 *
 * @package Pantheon\Terminus\Commands\Lock
 */
class DisableCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;
    use WorkflowProcessingTrait;

    /**
     * Disables HTTP basic authentication on the environment.
     *
     * @authorize
     * @interact
     *
     * @command lock:disable
     *
     * @param string $site_env Site & environment in the format `site-name.env`
     * @usage <site>.<env> Disables HTTP basic authentication on <site>'s <env> environment.
     *
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     */
    public function disable($site_env)
    {
        $env = $this->getEnv($site_env);
        $this->processWorkflow($env->getLock()->disable());
        $this->log()->notice(
            '{site}.{env} has been unlocked.',
            [
                'site' => $this->getSiteById($site_env)->getName(),
                'env' => $env->getName(),
            ]
        );
    }
}
