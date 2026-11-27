<?php

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'TokenResponse', required: ['user', 'token'], properties: [
    new OA\Property(property: 'user', ref: '#/components/schemas/User'),
    new OA\Property(property: 'token', description: 'Особистий токен Sanctum', type: 'string'),
], type: 'object')]
#[OA\Schema(schema: 'UtilityAccountInput', required: ['account_number', 'address'], properties: [
    new OA\Property(property: 'account_number', type: 'string', example: 'UA-OPENAPI-15001'),
    new OA\Property(property: 'address', type: 'string', example: 'Івано-Франківськ, вул. Шевченка, 15'),
    new OA\Property(property: 'service_ids', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
], type: 'object')]
#[OA\Schema(schema: 'UtilityAccount', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'account_number', type: 'string'),
    new OA\Property(property: 'address', type: 'string'),
    new OA\Property(property: 'owner_id', type: 'integer'),
    new OA\Property(property: 'status', type: 'string'),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'services', type: 'array', items: new OA\Items(properties: [new OA\Property(property: 'id', type: 'integer'), new OA\Property(property: 'name', type: 'string')], type: 'object')),
], type: 'object')]
#[OA\Schema(schema: 'ServiceProviderInput', required: ['name', 'edrpou', 'bank_account', 'contact_email'], properties: [
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'edrpou', type: 'string'),
    new OA\Property(property: 'bank_account', type: 'string'),
    new OA\Property(property: 'contact_email', type: 'string', format: 'email'),
], type: 'object')]
#[OA\Schema(schema: 'ServiceProvider', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'edrpou', type: 'string'),
    new OA\Property(property: 'contact_email', type: 'string', format: 'email'),
], type: 'object')]
#[OA\Schema(schema: 'UtilityServiceInput', required: ['name', 'service_provider_id', 'unit', 'tariff', 'active_from'], properties: [
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'service_provider_id', type: 'integer'),
    new OA\Property(property: 'unit', type: 'string'),
    new OA\Property(property: 'tariff', type: 'number', example: 120.50),
    new OA\Property(property: 'active_from', type: 'string', format: 'date'),
], type: 'object')]
#[OA\Schema(schema: 'UtilityService', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'service_provider_id', type: 'integer'),
    new OA\Property(property: 'unit', type: 'string'),
    new OA\Property(property: 'tariff', type: 'string', example: '120.50'),
    new OA\Property(property: 'active_from', type: 'string', format: 'date'),
], type: 'object')]
#[OA\Schema(schema: 'BillInput', required: ['utility_account_id', 'utility_service_id', 'period', 'amount', 'due_date'], properties: [
    new OA\Property(property: 'utility_account_id', type: 'integer'),
    new OA\Property(property: 'utility_service_id', type: 'integer'),
    new OA\Property(property: 'period', type: 'string'),
    new OA\Property(property: 'amount', type: 'number', example: 120.50),
    new OA\Property(property: 'due_date', type: 'string', format: 'date'),
], type: 'object')]
#[OA\Schema(schema: 'Bill', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'utility_account_id', type: 'integer'),
    new OA\Property(property: 'utility_service_id', type: 'integer'),
    new OA\Property(property: 'period', type: 'string'),
    new OA\Property(property: 'amount', type: 'string', example: '120.50'),
    new OA\Property(property: 'due_date', type: 'string', format: 'date'),
    new OA\Property(property: 'status', type: 'string'),
    new OA\Property(property: 'debt', type: 'string', example: '120.50'),
], type: 'object')]
#[OA\Schema(schema: 'MeterReadingInput', required: ['utility_account_id', 'previous_value', 'current_value'], properties: [
    new OA\Property(property: 'utility_account_id', type: 'integer'),
    new OA\Property(property: 'previous_value', type: 'number', example: 120.50),
    new OA\Property(property: 'current_value', type: 'number', example: 120.50),
], type: 'object')]
#[OA\Schema(schema: 'MeterReading', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'utility_account_id', type: 'integer'),
    new OA\Property(property: 'previous_value', type: 'string', example: '120.50'),
    new OA\Property(property: 'current_value', type: 'string', example: '120.50'),
    new OA\Property(property: 'status', type: 'string'),
    new OA\Property(property: 'submitted_at', type: 'string', format: 'date-time', nullable: true),
], type: 'object')]
#[OA\Schema(schema: 'PaymentInput', required: ['bill_id', 'amount', 'method'], properties: [
    new OA\Property(property: 'bill_id', type: 'integer'),
    new OA\Property(property: 'amount', type: 'number', example: 120.50),
    new OA\Property(property: 'method', type: 'string', enum: ['card', 'payment_service']),
], type: 'object')]
#[OA\Schema(schema: 'Payment', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'bill_id', type: 'integer'),
    new OA\Property(property: 'amount', type: 'string', example: '120.50'),
    new OA\Property(property: 'method', type: 'string', enum: ['card', 'payment_service']),
    new OA\Property(property: 'status', type: 'string'),
    new OA\Property(property: 'paid_at', type: 'string', format: 'date-time', nullable: true),
], type: 'object')]
#[OA\Schema(schema: 'ReceiptInput', required: ['payment_id', 'number'], properties: [
    new OA\Property(property: 'payment_id', type: 'integer'),
    new OA\Property(property: 'number', type: 'string'),
], type: 'object')]
#[OA\Schema(schema: 'Receipt', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'payment_id', type: 'integer'),
    new OA\Property(property: 'number', type: 'string'),
    new OA\Property(property: 'total', type: 'string', example: '120.50'),
    new OA\Property(property: 'issued_at', type: 'string', format: 'date-time', nullable: true),
], type: 'object')]
#[OA\Schema(schema: 'UserInput', required: ['name', 'email', 'password', 'role', 'status'], properties: [
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'email', type: 'string', format: 'email'),
    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
    new OA\Property(property: 'role', type: 'string', enum: ['customer', 'administrator']),
    new OA\Property(property: 'status', type: 'string', enum: ['active', 'blocked']),
], type: 'object')]
#[OA\Schema(schema: 'User', properties: [
    new OA\Property(property: 'id', type: 'integer'),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'email', type: 'string', format: 'email'),
    new OA\Property(property: 'role', type: 'string', enum: ['customer', 'administrator']),
    new OA\Property(property: 'status', type: 'string', enum: ['active', 'blocked']),
], type: 'object')]
#[OA\Schema(schema: 'UserUpdateInput', required: ['name', 'email', 'role', 'status'], properties: [
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'email', type: 'string', format: 'email'),
    new OA\Property(property: 'password', description: 'Новий пароль, якщо його потрібно змінити', type: 'string', format: 'password', nullable: true, minLength: 8),
    new OA\Property(property: 'role', type: 'string', enum: ['customer', 'administrator']),
    new OA\Property(property: 'status', type: 'string', enum: ['active', 'blocked']),
], type: 'object')]
class OpenApiSchemas {}
