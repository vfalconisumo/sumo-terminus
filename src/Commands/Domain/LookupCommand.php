<?php

namespace Pantheon\Terminus\Commands\Domain;

use Consolidation\OutputFormatters\StructuredData\PropertyList;
use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Exceptions\TerminusNotFoundException;

/**
 * Class LookupCommand.
 *
 * @package Pantheon\Terminus\Commands\Domain
 */
class LookupCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;

    /**
     * Displays site and environment with which a given domain is associated.
     * Note: Only sites for which the user is authorized will appear.
     *
     * @authorize
     * @interact
     *
     * @command domain:lookup
     *
     * @field-labels
     *     site_id: Site ID
     *     site_name: Site Name
     *     env_id: Environment ID
     * @return PropertyList
     *
     * @param string $domain Domain e.g. `example.com`
     *
     * @throws TerminusNotFoundException
     *
     * @usage <domain_name> Returns the site and environment associated with <domain_name> or displays not found.
     */
    public function lookup($domain)
    {
        $this->log()->notice('This operation may take a long time to run.');
        $sites = $this->sites()->all();
        foreach ($sites as $site) {
            $environments = $site->getEnvironments()->ids();
            foreach ($environments as $env_name) {
                try {
                    $env = $site->getEnvironments()->get($env_name);
                } catch (TerminusNotFoundException $e) {
                    $this->log()->warning(
                        'Site {site}: {message}',
                        ['site' => $site->id, 'message' => $e->getMessage()]
                    );
                    continue;
                }

                if ($env->getDomains()->has($domain)) {
                    return new PropertyList([
                        'site_id' => $site->id,
                        'site_name' => $site->getName(),
                        'env_id' => $env_name,
                    ]);
                }
            }
        }

        throw new TerminusNotFoundException(
            'Could not locate an environment with the domain {domain}.',
            compact('domain')
        );
    }
}
