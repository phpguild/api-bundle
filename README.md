# Symfony API Bundle

## Features

### Authentication
- Endpoint `[POST] /oauth/authenticate` for authenticate user with login/password and return JWT Bearer token
- Endpoint `[POST] /oauth/refresh_token` for authenticate user with refresh token and return JWT Bearer token
- Endpoint `[GET,PUT] /users/me` for authenticated user information

### Filters
- Filter Multisearch for search in multi fields `_search=Search%20term`
- Filter GeoDistance for search around user lat/lng `_distance[lat]=40.123&_distance[lng]=1.123&_distance[near]=30`

## Installation

Install with composer

    composer req phpguild/api-bundle

## Configure API Platform

Edit `config/packages/api_platform.yaml`

    api_platform:
        version: '1.0.0'
        mapping:
            paths: [ '%kernel.project_dir%/src/Entity' ]
        patch_formats:
            json: [ 'application/merge-patch+json' ]
        formats:
            jsonld:
                mime_types: [ 'application/ld+json' ]
            json:
                mime_types: [ 'application/json' ]
            html:
                mime_types: [ 'text/html' ]
        error_formats:
            jsonld:
                mime_types: [ 'application/ld+json' ]
            json:
                mime_types: [ 'application/json' ]
        swagger:
            versions: [ 3 ]
            api_keys:
                apiKey:
                    name: Authorization
                    type: header
        defaults:
            pagination_enabled: true
            pagination_items_per_page: 10
            pagination_maximum_items_per_page: 30
            pagination_client_partial: true
            pagination_client_items_per_page: true
        collection:
            exists_parameter_name: _exists
            order_parameter_name: _order
            pagination:
                page_parameter_name: _page
                items_per_page_parameter_name: _itemsPerPage
                partial_parameter_name: _partial

## Configure User authentication with JWT

Edit `config/packages/security.yaml`

    security:

        encoders:
            App\Entity\User:
                algorithm: auto

        providers:
            authentication_user_provider:
                entity:
                    class: App\Entity\User
                    property: username

            token_user_provider:
                entity:
                    class: App\Entity\User
                    property: id

        firewalls:
            dev:
                pattern: ^/(_(profiler|wdt)|css|images|js)/
                security: false

            oauth_refresh_token:
                pattern:  ^/oauth/refresh_token
                stateless: true
                anonymous: true
    
            oauth_authenticate:
                pattern:  ^/oauth/authenticate
                stateless: true
                anonymous: true
                json_login:
                    provider: authentication_user_provider
                    check_path: api_users_authentication
                    success_handler: lexik_jwt_authentication.handler.authentication_success
                    failure_handler: lexik_jwt_authentication.handler.authentication_failure
    
            api:
                pattern:  ^/
                stateless: true
                anonymous: true
                provider: token_user_provider
                guard:
                    authenticators:
                        - lexik_jwt_authentication.jwt_token_authenticator

            main:
                anonymous: true
                lazy: true

    access_control:
        - { path: ^/oauth/authenticate, methods: [ POST ], roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/oauth/refresh_token, methods: [ POST ], roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api/docs, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/api, roles: IS_AUTHENTICATED_FULLY }


Edit `config/packages/lexik_jwt_authentication.yaml`

    lexik_jwt_authentication:
        secret_key: '%env(resolve:JWT_SECRET_KEY)%'
        public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
        pass_phrase: '%env(JWT_PASSPHRASE)%'
        token_ttl: 3600
        user_identity_field: id
        token_extractors:
            authorization_header:
                enabled: true
                prefix:  Bearer
                name:    Authorization

Edit `config/api_platform.yaml`

    phpguild_api:
        resource: '@PhpGuildApiBundle/Resources/config/routes.yaml'
        prefix: /api

## Configure refresh Token

    gesdinet_jwt_refresh_token:
        firewall: api
        ttl: 2592000
        ttl_update: true
        user_identity_field: id
        user_provider: security.user.provider.concrete.token_user_provider

## Multisearch filter

### Usage

    use PhpGuild\ApiBundle\Doctrine\Orm\Filter\MultisearchFilter;
    
    /**
     * @ApiResource
     * @ApiFilter(MultisearchFilter::class, properties={"name", "description", "postalcode":"exact", "categories.name"})
     */
    class MyEntity
    {
        private string $name;
        private string $description;
        private string $postalcode;
        private Collection $categories;

## GeoDistance filter

### Configuration

Edit `config/packages/doctrine.yaml`

    doctrine:
        orm:
            dql:
                numeric_functions:
                    acos: DoctrineExtensions\Query\Mysql\Acos
                    cos: DoctrineExtensions\Query\Mysql\Cos
                    radians: DoctrineExtensions\Query\Mysql\Radians
                    sin: DoctrineExtensions\Query\Mysql\Sin

### Usage

    use PhpGuild\ApiBundle\Doctrine\Orm\Filter\GeoDistanceFilter;
    
    /*
     * @ApiResource
     * @ApiFilter(GeoDistanceFilter::class, attributes={"latPropertyName":"lat", "lngPropertyName":"lng"})
     */
    class MyEntity
    {
        private float $lat;
        private float $lng;
