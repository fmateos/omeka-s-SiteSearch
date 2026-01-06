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
        if ($this->shouldReturnAllSites($searchTerm)) {
            return $sites;
        }

        return $this->filterByTitle($sites, $searchTerm);
    }

    /**
     * Determine if search should return all sites
     *
     * @param string|null $searchTerm
     * @return bool
     */
    private function shouldReturnAllSites(?string $searchTerm): bool
    {
        return $searchTerm === null || trim($searchTerm) === '';
    }

    /**
     * Filter sites by title containing search term
     *
     * @param array $sites
     * @param string $searchTerm
     * @return array
     */
    private function filterByTitle(array $sites, string $searchTerm): array
    {
        $normalizedSearch = $this->normalizeSearchTerm($searchTerm);
        
        $filtered = array_filter($sites, function ($site) use ($normalizedSearch) {
            return $this->siteMatchesSearch($site, $normalizedSearch);
        });

        // Re-index array (array_values) to avoid gaps in keys
        return array_values($filtered);
    }

    /**
     * Normalize search term for comparison
     *
     * @param string $searchTerm
     * @return string
     */
    private function normalizeSearchTerm(string $searchTerm): string
    {
        return mb_strtolower(trim($searchTerm), 'UTF-8');
    }

    /**
     * Check if site matches search criteria
     *
     * @param array $site
     * @param string $normalizedSearch
     * @return bool
     */
    private function siteMatchesSearch(array $site, string $normalizedSearch): bool
    {
        if (!isset($site['title'])) {
            return false;
        }

        $normalizedTitle = mb_strtolower($site['title'], 'UTF-8');
        return mb_strpos($normalizedTitle, $normalizedSearch) !== false;
    }
}
