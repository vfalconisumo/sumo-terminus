<?php

namespace Pantheon\Terminus\Commands\Lock;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Commands\WorkflowProcessingTrait;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;

/**
 * Class EnableCommand.
 *
 * @package Pantheon\Terminus\Commands\Lock
 */
class EnableCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;
    use WorkflowProcessingTrait;

    /**
     * Enables HTTP basic authentication on the environment.
     * Note: HTTP basic authentication username and password are stored in plaintext.
     *
     * @authorize
     * @interact
     *
     * @command lock:enable
     *
     * @param string $site_env Site & environment in the format `site-name.env`
     * @param string $username HTTP basic authentication username
     * @param string $password HTTP basic authentication password
     * @usage <site>.<env> <username> <password> Enables HTTP basic authentication on <site>'s <env> environment with the username <username> and the password <password>.
     *
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     */
    public function enable($site_env, $username, $password)
    {
        $env = $this->getEnv($site_env);

        $this->processWorkflow($env->getLock()->enable(['username' => $username, 'password' => $password,]));
        $this->log()->notice(
            '{site}.{env} has been locked.',
            [
                'site' => $this->getSiteById($site_env)->getName(),
                'env' => $env->getName(),
            ]
        );
    }
}
