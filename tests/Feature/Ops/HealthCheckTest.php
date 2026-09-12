<?php

declare(strict_types=1);

it('exposes an unauthenticated health check for Render', function (): void {
    $this->get('/up')->assertOk();
});
