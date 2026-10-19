<?php

/**
 * Frontera de autenticación de los endpoints de tasación usados por
 * ModalTasacionesPropiedad.vue. Sin token válido (guard `auth:api`) la API responde 401.
 */
it('rechaza la creación de tasaciones sin token', function () {
    $this->postJson('/api/v1/propiedad/tasacion', [])->assertUnauthorized();
});

it('rechaza la actualización de tasaciones sin token', function () {
    $this->postJson('/api/v1/propiedad/tasacion/1', [])->assertUnauthorized();
});

it('rechaza la eliminación de tasaciones sin token', function () {
    $this->deleteJson('/api/v1/propiedad/tasacion/1')->assertUnauthorized();
});

it('rechaza el listado de tasaciones activas sin token', function () {
    $this->getJson('/api/v1/tasaciones/activas')->assertUnauthorized();
});
