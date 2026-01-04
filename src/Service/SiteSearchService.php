<?php
namespace SiteSearch\Service;

/**
 * Service for filtering sites by search criteria
 * 
 * This service provides search functionality for Omeka-S sites.
 * It is designed to be framework-agnostic for easy unit testing.
 */
class SiteSearchService
{
    /**
     * Filter sites by search term
     * 
     * Searches for sites whose title contains the search term.
     * Search is case-insensitive.
     * 
     * @param array $sites Array of site data with 'title' key
     * @param string|null $searchTerm Term to search for (null or empty = all sites)
     * @return array Filtered array of sites
     */
    public function filterSites(array $sites, ?string $searchTerm): array
    {
        // Empty search returns all sites
        if (empty($searchTerm)) {
            return $sites;
        }

        // Filter sites by title containing search term (case-insensitive)
        return array_values(
            array_filter($sites, function ($site) use ($searchTerm) {
                return stripos($site['title'], $searchTerm) !== false;
            })
        );
    }
}