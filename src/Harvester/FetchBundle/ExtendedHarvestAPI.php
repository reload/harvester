<?php

namespace Harvester\FetchBundle;

use \HarvestAPI;
use \Harvest_Range;
use \Harvest_Result;

/**
 * Extension of the HarvestAPI class to be able to modify some of the vendor functionality.
 */
class ExtendedHarvestAPI extends HarvestAPI {

    /**
     * get all user entries for given time range and for a particular project if specified
     *
     * @param int $user_id User Identifier
     * @param Harvest_Range $range Time Range
     * @param int $project_id Project identifier optional
     * @param string $updated_since Limit the results to updated entries
     * @return Harvest_Result
     */
    public function getUserEntries( $user_id, Harvest_Range $range, $project_id = null, $updated_since = null )
    {
        $url = "people/" . $user_id . "/entries?from=" . $range->from() . '&to=' . $range->to();
        if( ! is_null( $project_id ) ) {
            $url .= "&project_id=" . $project_id;
        }
        if( ! is_null ($updated_since) ) {
            $url .= "&updated_since=" . $updated_since;
        }
        return $this->performGET( $url, true );
    }
}
