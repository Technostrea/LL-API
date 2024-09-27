<?php

it('returns the OpenAPI documentation in JSON format', function () {
    $response = $this->get(route('documentation.json'));

    $response->assertStatus(200);

    $response->assertHeader('Content-Type', 'application/json');

    $response->assertJsonStructure([
        'openapi',
        'info' => [
            'title',
            'version'
        ],
        'paths',
    ]);
});
