<?php
namespace SiteSearchTest\Unit;

use PHPUnit\Framework\TestCase;
use SiteSearch\Service\SiteSearchService;

/**
 * Q1: Unit Tests - Technology-facing, supporting development
 * 
 * Tests the SiteSearchService in isolation with mock data.
 * These tests guide the implementation (TDD Red-Green-Refactor).
 */
class SiteSearchServiceTest extends TestCase
{
    private $service;
    private $mockSites;

    protected function setUp(): void
    {
        $this->service = new SiteSearchService();
        
        // Mock site data
        $this->mockSites = [
            ['id' => 1, 'title' => 'Digital Archive'],
            ['id' => 2, 'title' => 'Museum Collection'],
            ['id' => 3, 'title' => 'Archives Portal'],
            ['id' => 4, 'title' => 'Special Collections'],
        ];
    }

    /**
     * RED: This test will fail initially because the method doesn't exist
     */
    public function testSearchReturnsMatchingSites(): void
    {
        // Arrange
        $searchTerm = 'archive';
        
        // Act
        $result = $this->service->filterSites($this->mockSites, $searchTerm);
        
        // Assert
        $this->assertCount(2, $result, 'Should return 2 sites containing "archive"');
        $this->assertEquals('Digital Archive', $result[0]['title']);
        $this->assertEquals('Archives Portal', $result[1]['title']);
    }

    /**
     * Test case-insensitive search
     */
    public function testSearchIsCaseInsensitive(): void
    {
        // Arrange
        $searchTerm = 'MUSEUM';
        
        // Act
        $result = $this->service->filterSites($this->mockSites, $searchTerm);
        
        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals('Museum Collection', $result[0]['title']);
    }

    /**
     * Test empty search returns all sites
     */
    public function testEmptySearchReturnsAllSites(): void
    {
        // Arrange
        $searchTerm = '';
        
        // Act
        $result = $this->service->filterSites($this->mockSites, $searchTerm);
        
        // Assert
        $this->assertCount(4, $result, 'Empty search should return all sites');
    }

    /**
     * Test no matches returns empty array
     */
    public function testNoMatchesReturnsEmptyArray(): void
    {
        // Arrange
        $searchTerm = 'nonexistent';
        
        // Act
        $result = $this->service->filterSites($this->mockSites, $searchTerm);
        
        // Assert
        $this->assertEmpty($result, 'No matches should return empty array');
    }

    /**
     * Test null search term is treated as empty
     */
    public function testNullSearchReturnsAllSites(): void
    {
        // Arrange
        $searchTerm = null;
        
        // Act
        $result = $this->service->filterSites($this->mockSites, $searchTerm);
        
        // Assert
        $this->assertCount(4, $result);
    }
}