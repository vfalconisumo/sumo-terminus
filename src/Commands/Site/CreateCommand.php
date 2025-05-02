<?php

namespace Pantheon\Terminus\Commands\Site;

use Pantheon\Terminus\Commands\WorkflowProcessingTrait;
use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Helpers\Traits\WaitForWakeTrait;
use Pantheon\Terminus\Models\Environment;

/**
 * Class CreateCommand.
 *
 * @package Pantheon\Terminus\Commands\Site
 */
class CreateCommand extends SiteCommand
{
    use WorkflowProcessingTrait;
    use WaitForWakeTrait;

    /**
     * Creates a new site.
     *
     * @authorize
     * @interact
     *
     * @command site:create
     *
     * @param string $site_name Site name
     * @param string $label Site label
     * @param string $upstream_id Upstream name or UUID
     * @option org Organization name, label, or ID
     * @option region Specify the service region where the site should be
     *   created. See documentation for valid regions.
     *
     * @usage <site> <label> <upstream> Creates a new site named <site>, human-readably labeled <label>, using code from <upstream>.
     * @usage <site> <label> <upstream> --org=<org> Creates a new site named <site>, human-readably labeled <label>, using code from <upstream>, associated with <organization>.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Pantheon\Terminus\Exceptions\TerminusException
     * @throws \Exception
     */

    public function create($site_name, $label, $upstream_id, $options = ['org' => null, 'region' => null,])
    {
        if ($this->sites()->nameIsTaken($site_name)) {
            throw new TerminusException('The site name {site_name} is already taken.', compact('site_name'));
        }

        $workflow_options = [
            'label' => $label,
            'site_name' => $site_name,
        ];
        // If the user specified a region, then include it in the workflow
        // options. We'll allow the API to decide whether the region is valid.
        $region = $options['region'] ?? $this->config->get('command_site_options_region');
        if ($region) {
            $workflow_options['preferred_zone'] = $region;
        }

        $user = $this->session()->getUser();

        // Locate upstream.
        $upstream = $user->getUpstreams()->get($upstream_id);

        // Locate organization.
        if (!is_null($org_id = $options['org'])) {
            $org = $user->getOrganizationMemberships()->get($org_id)->getOrganization();
            $workflow_options['organization_id'] = $org->id;
        }

        // Create the site.
        $this->log()->notice('Creating a new site...');
        $workflow = $this->sites()->create($workflow_options);
        $this->processWorkflow($workflow);

        // Deploy the upstream.
        if ($site = $this->getSiteById($workflow->get('waiting_for_task')->site_id)) {
            $this->log()->notice('Deploying CMS...');
            $this->processWorkflow($site->deployProduct($upstream->id));
            $this->log()->notice('Waiting for site availability...');
            $env = $site->getEnvironments()->get('dev');
            if ($env instanceof Environment) {
                $this->waitForWake($env, $this->logger);
            }
        }
    }
}
