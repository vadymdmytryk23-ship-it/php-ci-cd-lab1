<?php

namespace App\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(path: '/register', summary: 'Створити профіль споживача й видати токен', tags: ['Auth'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['name', 'email', 'password', 'password_confirmation'], properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    ])),
    responses: [new OA\Response(response: 201, description: 'Профіль і токен', content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')), new OA\Response(response: 422, description: 'Помилка валідації')])]
#[OA\Post(path: '/login', summary: 'Отримати Bearer-токен', tags: ['Auth'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['email', 'password'], properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'customer@example.test'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
    ])),
    responses: [new OA\Response(response: 200, description: 'Профіль і токен', content: new OA\JsonContent(ref: '#/components/schemas/TokenResponse')), new OA\Response(response: 422, description: 'Помилка облікових даних')])]
#[OA\Post(path: '/logout', summary: 'Відкликати поточний токен', security: [['bearerAuth' => []]], tags: ['Auth'],
    responses: [new OA\Response(response: 204, description: 'Токен відкликано'), new OA\Response(response: 401, description: 'Потрібен токен')])]
#[OA\Get(path: '/profile', summary: 'Власний профіль', security: [['bearerAuth' => []]], tags: ['Auth'],
    responses: [new OA\Response(response: 200, description: 'Профіль', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')])), new OA\Response(response: 401, description: 'Потрібен токен')])]
#[OA\Get(path: '/utility-accounts', summary: 'Список записів', security: [[], ['bearerAuth' => []]], tags: ['Особові рахунки'],
    parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/UtilityAccount')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')]))])]
#[OA\Get(path: '/service-providers', summary: 'Список записів', tags: ['Постачальники'], parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ServiceProvider')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')]))])]
#[OA\Get(path: '/utility-services', summary: 'Список записів', tags: ['Послуги й тарифи'], parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/UtilityService')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')]))])]
#[OA\Get(path: '/bills', summary: 'Список записів', security: [['bearerAuth' => []]], tags: ['Нарахування'],
    parameters: [
        new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
        new OA\Parameter(name: 'account_id', in: 'query', schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['unpaid', 'paid'])),
    ],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Bill')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено')])]
#[OA\Get(path: '/meter-readings', summary: 'Список записів', security: [['bearerAuth' => []]], tags: ['Показники'],
    parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/MeterReading')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено')])]
#[OA\Get(path: '/payments', summary: 'Список записів', security: [['bearerAuth' => []]], tags: ['Платежі'],
    parameters: [
        new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
        new OA\Parameter(name: 'from', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
        new OA\Parameter(name: 'to', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
    ],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Payment')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено')])]
#[OA\Get(path: '/receipts', summary: 'Список записів', security: [['bearerAuth' => []]], tags: ['Квитанції'],
    parameters: [new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Receipt')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено')])]
#[OA\Get(path: '/users', summary: 'Список записів', security: [['bearerAuth' => []]], tags: ['Користувачі'],
    parameters: [
        new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
        new OA\Parameter(name: 'email', in: 'query', schema: new OA\Schema(type: 'string', format: 'email')),
    ],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')), new OA\Property(property: 'meta', type: 'object'), new OA\Property(property: 'links', type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено')])]
#[OA\Get(path: '/utility-accounts/{id}', summary: 'Деталі запису', security: [[], ['bearerAuth' => []]], tags: ['Особові рахунки'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityAccount')])), new OA\Response(response: 404, description: 'Запис не знайдено')])]
#[OA\Get(path: '/service-providers/{id}', summary: 'Деталі запису', tags: ['Постачальники'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ServiceProvider')])), new OA\Response(response: 404, description: 'Запис не знайдено')])]
#[OA\Get(path: '/utility-services/{id}', summary: 'Деталі запису', tags: ['Послуги й тарифи'], parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityService')])), new OA\Response(response: 404, description: 'Запис не знайдено')])]
#[OA\Get(path: '/bills/{id}', summary: 'Деталі запису', security: [['bearerAuth' => []]], tags: ['Нарахування'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Bill')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
#[OA\Get(path: '/meter-readings/{id}', summary: 'Деталі запису', security: [['bearerAuth' => []]], tags: ['Показники'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MeterReading')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
#[OA\Get(path: '/payments/{id}', summary: 'Деталі запису', security: [['bearerAuth' => []]], tags: ['Платежі'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Payment')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
#[OA\Get(path: '/receipts/{id}', summary: 'Деталі запису', security: [['bearerAuth' => []]], tags: ['Квитанції'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Receipt')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
#[OA\Get(path: '/users/{id}', summary: 'Деталі запису', security: [['bearerAuth' => []]], tags: ['Користувачі'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
#[OA\Post(path: '/utility-accounts', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Особові рахунки'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UtilityAccountInput', description: 'Поля запису')),
    responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityAccount')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Post(path: '/service-providers', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Постачальники'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ServiceProviderInput', description: 'Поля запису')),
    responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ServiceProvider')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Post(path: '/utility-services', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Послуги й тарифи'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UtilityServiceInput', description: 'Поля запису')),
    responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityService')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Post(path: '/bills', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Нарахування'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/BillInput', description: 'Поля запису')),
    responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Bill')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Post(path: '/meter-readings', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Показники'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/MeterReadingInput', description: 'Поля запису')),
    responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MeterReading')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Post(path: '/payments', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Платежі'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PaymentInput', description: 'Поля запису')),
    responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Payment')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Post(path: '/receipts', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Квитанції'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ReceiptInput', description: 'Поля запису')),
    responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Receipt')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Post(path: '/users', summary: 'Створити запис', security: [['bearerAuth' => []]], tags: ['Користувачі'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UserInput', description: 'Поля запису')),
    responses: [new OA\Response(response: 201, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Put(path: '/utility-accounts/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Особові рахунки'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UtilityAccountInput', description: 'Поля запису')),
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityAccount')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Put(path: '/service-providers/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Постачальники'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ServiceProviderInput', description: 'Поля запису')),
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ServiceProvider')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Put(path: '/utility-services/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Послуги й тарифи'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UtilityServiceInput', description: 'Поля запису')),
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/UtilityService')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Put(path: '/bills/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Нарахування'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/BillInput', description: 'Поля запису')),
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Bill')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Put(path: '/meter-readings/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Показники'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/MeterReadingInput', description: 'Поля запису')),
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/MeterReading')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Put(path: '/payments/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Платежі'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PaymentInput', description: 'Поля запису')),
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Payment')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Put(path: '/receipts/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Квитанції'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ReceiptInput', description: 'Поля запису')),
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Receipt')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Put(path: '/users/{id}', summary: 'Оновити запис', security: [['bearerAuth' => []]], tags: ['Користувачі'],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UserUpdateInput')),
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Успішна відповідь', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Delete(path: '/utility-accounts/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Особові рахунки'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Delete(path: '/service-providers/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Постачальники'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Delete(path: '/utility-services/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Послуги й тарифи'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Delete(path: '/bills/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Нарахування'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Delete(path: '/meter-readings/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Показники'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Delete(path: '/payments/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Платежі'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Delete(path: '/receipts/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Квитанції'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Delete(path: '/users/{id}', summary: 'Видалити запис', security: [['bearerAuth' => []]], tags: ['Користувачі'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 204, description: 'Запис видалено'), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Доступ заборонено'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 422, description: 'Перевірте дані або обмеження запису')])]
#[OA\Post(path: '/payments/{id}/confirm', summary: 'Підтвердити навчальний платіж', security: [['bearerAuth' => []]], tags: ['Платежі'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Платіж', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Payment')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Чужий платіж'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 409, description: 'Несумісний стан'), new OA\Response(response: 422, description: 'Недостатній залишок')])]
#[OA\Post(path: '/payments/{id}/cancel', summary: 'Скасувати навчальний платіж', security: [['bearerAuth' => []]], tags: ['Платежі'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Платіж', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Payment')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Чужий платіж'), new OA\Response(response: 404, description: 'Запис не знайдено'), new OA\Response(response: 409, description: 'Несумісний стан')])]
#[OA\Get(path: '/receipts/{id}/download', summary: 'Завантажити квитанцію', security: [['bearerAuth' => []]], tags: ['Квитанції'],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
    responses: [new OA\Response(response: 200, description: 'Текстовий файл', content: new OA\MediaType(mediaType: 'text/plain', schema: new OA\Schema(type: 'string'))), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Чужа квитанція'), new OA\Response(response: 404, description: 'Запис не знайдено')])]
#[OA\Get(path: '/statistics', summary: 'Зведені показники для адміністратора', security: [['bearerAuth' => []]], tags: ['Користувачі'],
    responses: [new OA\Response(response: 200, description: 'Статистика', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', properties: [new OA\Property(property: 'users', type: 'integer'), new OA\Property(property: 'unpaid_bills', type: 'integer'), new OA\Property(property: 'paid', type: 'number')], type: 'object')])), new OA\Response(response: 401, description: 'Потрібен токен'), new OA\Response(response: 403, description: 'Лише адміністратор')])]
class Operations {}
