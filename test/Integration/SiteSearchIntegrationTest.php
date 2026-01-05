<?php
namespace SiteSearchTest\Integration;

use PHPUnit\Framework\TestCase;
use SiteSearch\Service\SiteSearchService;

/**
 * Q2: Acceptance/Integration Tests - Business-facing, critique product
 * 
 * Tests the complete workflow from user input to filtered results.
 * Validates that the feature works as expected from business perspective.
 */
class SiteSearchIntegrationTest extends TestCase
{
    private $service;
    private $sampleSites;

    protected function setUp(): void
    {
        $this->service = new SiteSearchService();
        
        // Sample data representing real Omeka-S sites
        $this->sampleSites = [
            ['id' => 1, 'slug' => 'digital-library', 'title' => 'Digital Library'],
            ['id' => 2, 'slug' => 'museum-catalog', 'title' => 'Museum Catalog'],
            ['id' => 3, 'slug' => 'archives', 'title' => 'Historical Archives'],
            ['id' => 4, 'slug' => 'collections', 'title' => 'Special Collections'],
            ['id' => 5, 'slug' => 'library-portal', 'title' => 'Library Portal'],
        ];
    }

    /**
     * Scenario: Admin searches for sites containing "library"
     * 
     * Given I am on the sites administration page
     * When I search for "library"
     * Then I should see "Digital Library" and "Library Portal"
     * And I should not see other sites
     */
    public function testAdminCanSearchSitesByTerm(): void
    {
        // Given: Admin has entered "library" in search field
        $userInput = 'library';
        
        // When: Search is executed
        $results = $this->service->filterSites($this->sampleSites, $userInput);
        
        // Then: Only matching sites are returned
        $this->assertCount(2, $results, 'Should find exactly 2 sites with "library"');
        
        $titles = array_column($results, 'title');
        $this->assertContains('Digital Library', $titles);
        $this->assertContains('Library Portal', $titles);
        $this->assertNotContains('Museum Catalog', $titles);
    }

    /**
     * Scenario: Admin searches with mixed case
     * 
     * Given I am on the sites administration page
     * When I search for "MUSEUM" in uppercase
     * Then I should see "Museum Catalog"
     */
    public function testSearchIgnoresCase(): void
    {
        // Given: User types in uppercase
        $userInput = 'MUSEUM';
        
        // When: Search is executed
        $results = $this->service->filterSites($this->sampleSites, $userInput);
        
        // Then: Case is ignored
        $this->assertCount(1, $results);
        $this->assertEquals('Museum Catalog', $results[0]['title']);
    }

    /**
     * Scenario: Admin clears search to see all sites
     * 
     * Given I am on the sites administration page
     * And I previously searched for something
     * When I clear the search field
     * Then I should see all sites
     */
    public function testClearingSearchShowsAllSites(): void
    {
        // Given: Search was previously used
        $previousSearch = $this->service->filterSites($this->sampleSites, 'library');
        $this->assertLessThan(count($this->sampleSites), count($previousSearch));
        
        // When: Search is cleared (empty string)
        $results = $this->service->filterSites($this->sampleSites, '');
        
        // Then: All sites are shown
        $this->assertCount(
            count($this->sampleSites),
            $results,
            'Clearing search should show all sites'
        );
    }

    /**
     * Scenario: Admin searches for non-existent site
     * 
     * Given I am on the sites administration page
     * When I search for a term that matches no sites
     * Then I should see "No sites found" message
     */
    public function testSearchWithNoResultsReturnsEmpty(): void
    {
        // Given: User searches for something that doesn't exist
        $userInput = 'nonexistent site name';
        
        // When: Search is executed
        $results = $this->service->filterSites($this->sampleSites, $userInput);
        
        // Then: Empty result set is returned
        $this->assertEmpty(
            $results,
            'Search with no matches should return empty array for "no results" UI'
        );
    }

    /**
     * Scenario: Search handles special characters safely
     * 
     * Given I am on the sites administration page
     * When I search with special characters
     * Then the search should not break
     */
    public function testSearchHandlesSpecialCharacters(): void
    {
        // Given: Sites with special characters
        $sitesWithSpecial = [
            ['id' => 1, 'title' => 'Archive & Museum'],
            ['id' => 2, 'title' => 'Digital (Library)'],
            ['id' => 3, 'title' => 'Collection: Arts'],
        ];
        
        // When: User searches with special char
        $results = $this->service->filterSites($sitesWithSpecial, '&');
        
        // Then: Search works correctly
        $this->assertCount(1, $results);
        $this->assertEquals('Archive & Museum', $results[0]['title']);
    }

    /**
     * Scenario: Search handles accented characters
     * 
     * Given I am on the sites administration page
     * When I search with accented characters
     * Then the search should match correctly (case-insensitive but accent-sensitive)
     */
    public function testSearchHandlesAccentedCharacters(): void
    {
        // Given: Sites with Spanish/international characters
        $sitesWithAccents = [
            ['id' => 1, 'title' => 'Colección Histórica'],
            ['id' => 2, 'title' => 'Bibliothèque Numérique'],
            ['id' => 3, 'title' => 'Archiv für Geschichte'],
        ];
        
        // When: User searches with exact accented term (lowercase)
        $results = $this->service->filterSites($sitesWithAccents, 'colección');
        
        // Then: Case-insensitive search works with accents
        $this->assertCount(1, $results);
        $this->assertEquals('Colección Histórica', $results[0]['title']);
        
        // When: User searches with partial term including accent
        $results = $this->service->filterSites($sitesWithAccents, 'histórica');
        
        // Then: Finds the site
        $this->assertCount(1, $results);
        $this->assertEquals('Colección Histórica', $results[0]['title']);
        
        // When: User searches WITHOUT accent
        $results = $this->service->filterSites($sitesWithAccents, 'coleccion');
        
        // Then: Does NOT find it (accent-sensitive is expected behavior)
        $this->assertEmpty($results, 'Search without accents should not match accented text');
    }
}