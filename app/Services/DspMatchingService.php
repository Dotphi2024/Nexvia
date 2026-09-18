<?php

namespace App\Services;

use App\Models\DspApplication;
use Illuminate\Support\Collection;

class DspMatchingService
{
    /**
     * Find all approved DSP partners servicing the given pincode, district, or state.
     *
     * @param string|null $pincode
     * @param string|null $district
     * @param string|null $state
     * @return Collection
     */
    public function findAvailableDsps(?string $pincode, ?string $district = null, ?string $state = null): Collection
    {
        $cleanPincode = trim((string)$pincode);
        $cleanDistrict = trim((string)$district);
        $cleanState = trim((string)$state);

        $approvedQuery = DspApplication::where('status', 'approved');

        $allApproved = $approvedQuery->get();

        if ($allApproved->isEmpty()) {
            // Also check under_review / pending for staging/fallback if no approved exists
            $allApproved = DspApplication::whereIn('status', ['approved', 'under_review'])->get();
        }

        $exactMatches = collect();
        $districtMatches = collect();
        $stateMatches = collect();
        $fallbackMatches = collect();

        foreach ($allApproved as $dsp) {
            // 1. Exact pincode match in serviced pincodes or premises pincode
            if (!empty($cleanPincode) && $dsp->isServicingPincode($cleanPincode)) {
                $dsp->match_type = 'exact_pincode';
                $dsp->match_label = 'Direct Area Partner';
                $exactMatches->push($dsp);
                continue;
            }

            // 2. District match
            if (!empty($cleanDistrict) && !empty($dsp->district) && strcasecmp(trim($dsp->district), $cleanDistrict) === 0) {
                $dsp->match_type = 'district';
                $dsp->match_label = 'District Service Partner';
                $districtMatches->push($dsp);
                continue;
            }

            // 3. State match
            if (!empty($cleanState) && !empty($dsp->state) && strcasecmp(trim($dsp->state), $cleanState) === 0) {
                $dsp->match_type = 'state';
                $dsp->match_label = 'State Regional Partner';
                $stateMatches->push($dsp);
                continue;
            }

            $dsp->match_type = 'authorized_hub';
            $dsp->match_label = 'Authorised Regional Hub';
            $fallbackMatches->push($dsp);
        }

        // Return combined in order of proximity/relevance
        return $exactMatches
            ->merge($districtMatches)
            ->merge($stateMatches)
            ->merge($fallbackMatches);
    }

    /**
     * Get single best matching DSP for an order/booking.
     */
    public function getBestMatchingDsp(?string $pincode, ?string $district = null, ?string $state = null): ?DspApplication
    {
        return $this->findAvailableDsps($pincode, $district, $state)->first();
    }
}
