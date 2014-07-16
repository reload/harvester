<?php

namespace Harvester\FetchBundle;

use Mattvick\HarvestAppBundle\Services\HarvestApp;
use Harvester\FetchBundle\ExtendedHarvestAPI;

/**
 * Extended proxy class of the "HarvestApp", to get our extended API.
 */
class HarvestAdapter extends HarvestApp {

  private $extended_api;

  /**
   * @param ExtendedHarvestApi $extended_api Extended HarvestAPI API client instance
   * @param object $user
   * @param string $password
   * @param string $account
   * @param boolean $ssl
   * @param boolean $mode
   */
  public function __construct(ExtendedHarvestAPI $extended_api, $user, $password, $account, $ssl, $mode)
  {
    $this->extended_api = $extended_api;

    // Set parameters
    $this->extended_api->setUser($user);
    $this->extended_api->setPassword($password);
    $this->extended_api->setAccount($account);
    $this->extended_api->setSSL($ssl);
    $this->extended_api->setRetryMode($mode);
  }

  /**
   * Get oAuth client
   *
   * @return ExtendedHarvestAPI
   */
  public function getApi()
  {
    return $this->extended_api;
  }
}
