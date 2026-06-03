<?php

declare(strict_types=1);

namespace HyderKamran\VoipNow\Tests;

use Mockery;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;
use HyderKamran\VoipNow\VoipNowClient;
use HyderKamran\VoipNow\VoipNowFacade;
use HyderKamran\VoipNow\VoipNowSoapClient;
use HyderKamran\VoipNow\VoipNowServiceProvider;
use HyderKamran\VoipNow\Contracts\ConnectorInterface;
use HyderKamran\VoipNow\Exception\VoipNowException;

class VoipNowClientTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [VoipNowServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['VoipNow' => VoipNowFacade::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('voipnow.adapter', 'rest');
        $app['config']->set('voipnow.voip_domain', 'https://voipnow.example.com');
        $app['config']->set('voipnow.voip_key', 'test-key');
        $app['config']->set('voipnow.voip_secret', 'test-secret');
    }

    /**
     * Build a mock ConnectorInterface and bind it in the container.
     */
    protected function mockConnector(): MockInterface
    {
        $mock = Mockery::mock(ConnectorInterface::class);
        $mock->shouldReceive('connect')->andReturnSelf();
        $this->app->instance(ConnectorInterface::class, $mock);
        return $mock;
    }

    /**
     * Build a mock VoipNowSoapClient and bind it in the container.
     */
    protected function mockSoapClient(): MockInterface
    {
        $mock = Mockery::mock(VoipNowSoapClient::class);
        $this->app->instance(VoipNowSoapClient::class, $mock);
        return $mock;
    }

    /**
     * Helper: build a VoipNowClient using the given connector mock.
     */
    protected function makeClient(MockInterface $connector): VoipNowClient
    {
        $soapMock = $this->mockSoapClient();
        return new VoipNowClient($this->app['config'], $connector, $soapMock);
    }

    // -----------------------------------------------------------------------
    // Container resolution
    // -----------------------------------------------------------------------

    /** @test */
    public function it_can_resolve_the_voipnow_client_from_the_container(): void
    {
        $client = $this->app->make('voipnow');
        $this->assertInstanceOf(VoipNowClient::class, $client);
    }

    /** @test */
    public function it_can_resolve_the_soap_client_from_the_container(): void
    {
        $soapClient = $this->app->make('voipnow.soap');
        $this->assertInstanceOf(VoipNowSoapClient::class, $soapClient);
    }

    // -----------------------------------------------------------------------
    // Core HTTP verb delegation
    // -----------------------------------------------------------------------

    /** @test */
    public function it_delegates_get_to_connector(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('get')
            ->once()
            ->with('organizations', [])
            ->andReturn(['data' => []]);

        $result = $this->makeClient($mock)->get('organizations');
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_delegates_post_to_connector(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('post')
            ->once()
            ->with('organizations', ['name' => 'Acme'])
            ->andReturn(['id' => 1, 'name' => 'Acme']);

        $result = $this->makeClient($mock)->post('organizations', ['name' => 'Acme']);
        $this->assertEquals(['id' => 1, 'name' => 'Acme'], $result);
    }

    /** @test */
    public function it_delegates_put_to_connector(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('put')
            ->once()
            ->with('organizations/1', ['name' => 'Acme Updated'])
            ->andReturn(['id' => 1]);

        $result = $this->makeClient($mock)->put('organizations/1', ['name' => 'Acme Updated']);
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_delegates_delete_to_connector(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('delete')
            ->once()
            ->with('organizations/1', [])
            ->andReturn([]);

        $this->makeClient($mock)->delete('organizations/1');
        $this->assertTrue(true);
    }

    // -----------------------------------------------------------------------
    // Resource helpers
    // -----------------------------------------------------------------------

    /** @test */
    public function it_exposes_get_service_providers_helper(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('get')
            ->once()
            ->with('service-providers', [])
            ->andReturn([['id' => 1]]);

        $result = $this->makeClient($mock)->GetServiceProviders();
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_exposes_get_organization_details_helper_with_id(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('get')
            ->once()
            ->with('organizations/42', [])
            ->andReturn(['id' => 42]);

        $result = $this->makeClient($mock)->GetOrganizationDetails(['ID' => 42]);
        $this->assertEquals(['id' => 42], $result);
    }

    /** @test */
    public function it_exposes_get_organization_details_helper_with_identifier(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('get')
            ->once()
            ->with('organizations/acme', [])
            ->andReturn(['identifier' => 'acme']);

        $result = $this->makeClient($mock)->GetOrganizationDetails(['identifier' => 'acme']);
        $this->assertIsArray($result);
    }

    /** @test */
    public function it_exposes_find_helper(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('get')
            ->once()
            ->with('extensions/5', [])
            ->andReturn(['id' => 5]);

        $result = $this->makeClient($mock)->find('extensions', 5);
        $this->assertEquals(['id' => 5], $result);
    }

    /** @test */
    public function it_exposes_paginate_helper(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('get')
            ->once()
            ->with('users', ['limit' => 20, 'offset' => 40])
            ->andReturn(['data' => [], 'total' => 0]);

        $result = $this->makeClient($mock)->paginate('users', 3, 20);
        $this->assertIsArray($result);
    }

    // -----------------------------------------------------------------------
    // soap() accessor
    // -----------------------------------------------------------------------

    /** @test */
    public function it_exposes_the_soap_client_via_soap_accessor(): void
    {
        $soapMock = $this->mockSoapClient();
        $connector = $this->mockConnector();
        $client = new VoipNowClient($this->app['config'], $connector, $soapMock);

        $this->assertSame($soapMock, $client->soap());
    }

    // -----------------------------------------------------------------------
    // Magic method fallback
    // -----------------------------------------------------------------------

    /** @test */
    public function it_resolves_magic_get_to_kebab_endpoint(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('get')
            ->once()
            ->with('call-queues', [])
            ->andReturn([]);

        $this->makeClient($mock)->GetCallQueues();
    }

    /** @test */
    public function it_resolves_magic_add_to_post(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('post')
            ->once()
            ->with('extensions', ['number' => '101'])
            ->andReturn(['id' => 5]);

        $this->makeClient($mock)->AddExtensions(['number' => '101']);
    }

    /** @test */
    public function it_resolves_magic_remove_to_delete(): void
    {
        $mock = $this->mockConnector();
        $mock->shouldReceive('delete')
            ->once()
            ->with('extensions', ['id' => 9])
            ->andReturn([]);

        $this->makeClient($mock)->RemoveExtensions(['id' => 9]);
    }

    // -----------------------------------------------------------------------
    // Exception handling
    // -----------------------------------------------------------------------

    /** @test */
    public function it_throws_when_organization_details_called_without_id(): void
    {
        $this->expectException(VoipNowException::class);

        $mock = $this->mockConnector();
        $this->makeClient($mock)->GetOrganizationDetails([]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
