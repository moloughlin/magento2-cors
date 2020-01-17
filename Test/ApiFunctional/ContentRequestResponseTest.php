<?php
/**
 * Copyright © Graycore, LLC. All rights reserved.
 * See LICENSE.md for details.
 */
namespace Graycore\Cors\Test\Integration;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\HttpClient\CurlClient;
use PHPUnit\Framework\TestCase;


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
class ContentRequestResponseTest extends TestCase
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
     * @magentoConfigFixture default_store web/graphql/cors_allowed_origins https://www.example.com
     */
    public function testItdoesNotAddAnyCrossOriginHeadersToATypicalRequest()
    {
        $curl = $this->_getCurlClient();
        $this->expectExceptionCode(405);

        $response = $curl->invokeApi(TESTS_BASE_URL, [CURLOPT_CUSTOMREQUEST => 'OPTIONS']);
    }
}