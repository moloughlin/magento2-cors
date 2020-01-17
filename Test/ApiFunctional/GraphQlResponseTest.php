<?php
/**
 * Copyright © Graycore, LLC. All rights reserved.
 * See LICENSE.md for details.
 */
namespace Graycore\Cors\Test\Integration;

use Magento\Framework\App\Response\Http;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\HttpClient\CurlClient;

/**
 * Tests that the responses to GraphQl API requests
 * properly respond with the CORS headers in the
 * default configuration
 * @category  PHP
 * @package   Graycore_Cors
 * @author    Graycore <damien@graycore.io>
 * @copyright Graycore, LLC (https://www.graycore.io/)
 * @license   MIT https://github.com/graycoreio/magento2-cors/license
 * @link      https://github.com/graycoreio/magento2-cors
 */
class GraphQlResponseTest extends GraphQlAbstract
{
    /** 
     * @return \Magento\TestFramework\TestCase\HttpClient\CurlClient
     */
    private function _getCurlClient() 
    {
        return Bootstrap::getObjectManager()->get(
            CurlClient::class
        );
    }

    /**
     * @return ResponseInterface
     */
    private function _dispatchToGraphQlApiWithOrigin(string $origin)
    {
        $productSku='simple2';
        $query
            = <<<QUERY
 {
           products(filter: {sku: {eq: "{$productSku}"}})
           {
               items {
                   id
                   name
                   sku
               }
           }
       }
QUERY;
        return $this->graphQlQueryWithResponseHeaders(
            $query, [], 
            'searchProducts', 
            [
                'Origin' => $origin,
                'Content-Type' => 'application/json'
            ]
        );
    }

    /**
     */
    public function testItDoesNotAddAnyCrossOriginHeadersOutOfTheBox()
    {
        $response = $this->_dispatchToGraphQlApiWithOrigin("https://www.example.com");
        
        $this->assertArrayNotHasKey('Access-Control-Allow-Origin', $response['headers']);
        $this->assertArrayNotHasKey('Access-Control-Max-Age', $response['headers']);
        $this->assertArrayNotHasKey('Access-Control-Allow-Methods', $response['headers']);
        $this->assertArrayNotHasKey('Access-Control-Allow-Headers', $response['headers']);
    }

    /**
     * @magentoConfigFixture default_store web/graphql/cors_allowed_origins https://www.example.com
     * @magentoConfigFixture default_store web/graphql/cors_allowed_headers Some-Header
     */
    public function testTheGraphQlResponseContainsCrossOriginHeaders()
    {
        $this->markTestIncomplete('This test should pass but the fixture doesnt work.');

        /** @var [] $response */
        $response = $this->_dispatchToGraphQlApiWithOrigin("https://www.example.com");

        //Skipped due to Magento ApiFunctionalBug (https://github.com/magento/magento2/issues/26425)
        //$this->assertArrayHasKey('Access-Control-Allow-Origin', $response['headers']);
        $this->assertArrayHasKey('Access-Control-Allow-Methods', $response['headers']);
        $this->assertArrayHasKey('Access-Control-Allow-Headers', $response['headers']);
        $this->assertArrayHasKey('Access-Control-Max-Age', $response['headers']);
    }

    public function testTheGraphQlApiWillRespondToAnOptionsRequestWithA200Response()
    {
        $curl = $this->_getCurlClient();
        $endpoint = TESTS_BASE_URL . '/graphql';

        $response = $curl->invokeApi($endpoint, [CURLOPT_CUSTOMREQUEST => 'OPTIONS']);
        $this->assertSame(200, $response['meta']['http_code']);
        $this->assertNotContains('Access-Control-Allow-Origin', $response['header']);
    }

    /**
     * @magentoConfigFixture default_store web/graphql/cors_allowed_origins https://www.example.com
     */
    public function testTheGraphQlApiWillRespondToAnOptionsRequestWithCorsHeadersOnTheResponse()
    {
        $this->markTestIncomplete('This test should pass but the fixture doesnt work.');

        $curl = $this->_getCurlClient();
        $endpoint = TESTS_BASE_URL . '/graphql';

        $response = $curl->invokeApi($endpoint, [CURLOPT_CUSTOMREQUEST => 'OPTIONS'], [
            'Origin: https://www.example.com'
        ]);

        //Skipped due to Magento ApiFunctionalBug (https://github.com/magento/magento2/issues/26425)
        //$this->assertContains('Access-Control-Allow-Origin', $response['headers']);
        $this->assertContains('Access-Control-Allow-Methods', $response['header']);
        $this->assertContains('Access-Control-Max-Age', $response['header']);
    }
}
