<?php

namespace App;

use OpenApi\Attributes as OA;

#[
    OA\Info(
        version: '1.0.0',
        title: 'E-Commerce API',
        description: 'API for the Laravel E-Commerce application. Provides endpoints for browsing products, managing carts/wishlists, placing orders, and customer account management.',
    ),
    OA\Server(
        url: L5_SWAGGER_CONST_HOST,
        description: 'Application server',
    ),
    OA\Server(
        url: 'http://localhost:8000',
        description: 'Local development server',
    ),
    OA\SecurityScheme(
        securityScheme: 'sanctum',
        type: 'http',
        scheme: 'bearer',
        description: 'Enter your Bearer token from POST /api/login or POST /api/register',
    ),
]
class OpenApi {}

/*
|--------------------------------------------------------------------------
| Shared Response Schemas
|--------------------------------------------------------------------------
*/

#[
    OA\Schema(
        schema: 'User',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
            new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
            new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+1234567890'),
            new OA\Property(property: 'avatar', type: 'string', nullable: true),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ],
    ),
]
class UserSchema {}

#[
    OA\Schema(
        schema: 'Category',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
            new OA\Property(property: 'slug', type: 'string', example: 'electronics'),
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(property: 'image_url', type: 'string', nullable: true, format: 'uri'),
            new OA\Property(property: 'products_count', type: 'integer', example: 12),
            new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
            new OA\Property(property: 'parent_name', type: 'string', nullable: true),
        ],
    ),
]
class CategorySchema {}

#[
    OA\Schema(
        schema: 'Product',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Wireless Headphones'),
            new OA\Property(property: 'slug', type: 'string', example: 'wireless-headphones'),
            new OA\Property(property: 'price', type: 'number', format: 'float', example: 99.99),
            new OA\Property(property: 'sale_price', type: 'number', format: 'float', nullable: true, example: 79.99),
            new OA\Property(property: 'display_price', type: 'number', format: 'float', example: 79.99),
            new OA\Property(property: 'on_sale', type: 'boolean', example: true),
            new OA\Property(property: 'image_url', type: 'string', nullable: true, format: 'uri'),
            new OA\Property(property: 'stock', type: 'integer', example: 50),
            new OA\Property(property: 'in_stock', type: 'boolean', example: true),
            new OA\Property(property: 'category', properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
                new OA\Property(property: 'slug', type: 'string', example: 'electronics'),
            ], type: 'object'),
        ],
    ),
]
class ProductSchema {}

#[
    OA\Schema(
        schema: 'ProductDetail',
        allOf: [
            new OA\Schema(ref: '#/components/schemas/Product'),
            new OA\Schema(type: 'object', properties: [
                new OA\Property(property: 'description', type: 'string', example: 'High-quality wireless headphones with noise cancellation.'),
                new OA\Property(property: 'sku', type: 'string', example: 'WH-1000XM4'),
                new OA\Property(property: 'stock', type: 'integer', example: 50),
                new OA\Property(property: 'images', type: 'array', items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'url', type: 'string', format: 'uri'),
                    new OA\Property(property: 'alt', type: 'string'),
                ], type: 'object')),
                new OA\Property(property: 'reviews', type: 'array', items: new OA\Items(ref: '#/components/schemas/Review')),
                new OA\Property(property: 'avg_rating', type: 'number', format: 'float', nullable: true, example: 4.5),
                new OA\Property(property: 'review_count', type: 'integer', example: 10),
            ]),
        ],
    ),
]
class ProductDetailSchema {}

#[
    OA\Schema(
        schema: 'CartItem',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'product_id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Wireless Headphones'),
            new OA\Property(property: 'slug', type: 'string', example: 'wireless-headphones'),
            new OA\Property(property: 'image_url', type: 'string', nullable: true, format: 'uri'),
            new OA\Property(property: 'price', type: 'number', format: 'float', example: 79.99),
            new OA\Property(property: 'quantity', type: 'integer', example: 2),
            new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 159.98),
            new OA\Property(property: 'stock', type: 'integer', example: 50),
        ],
    ),
]
class CartItemSchema {}

#[
    OA\Schema(
        schema: 'WishlistItem',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'product_id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Wireless Headphones'),
            new OA\Property(property: 'slug', type: 'string', example: 'wireless-headphones'),
            new OA\Property(property: 'price', type: 'number', format: 'float', example: 99.99),
            new OA\Property(property: 'sale_price', type: 'number', format: 'float', nullable: true, example: 79.99),
            new OA\Property(property: 'image_url', type: 'string', nullable: true, format: 'uri'),
            new OA\Property(property: 'in_stock', type: 'boolean', example: true),
            new OA\Property(property: 'added_at', type: 'string', format: 'date-time'),
        ],
    ),
]
class WishlistItemSchema {}

#[
    OA\Schema(
        schema: 'OrderItem',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'product_id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Wireless Headphones'),
            new OA\Property(property: 'slug', type: 'string', example: 'wireless-headphones'),
            new OA\Property(property: 'image_url', type: 'string', nullable: true),
            new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 79.99),
            new OA\Property(property: 'quantity', type: 'integer', example: 2),
            new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 159.98),
        ],
    ),
]
class OrderItemSchema {}

#[
    OA\Schema(
        schema: 'Order',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'order_number', type: 'string', example: 'ORD-ABC123XYZ'),
            new OA\Property(property: 'status', type: 'string', example: 'pending', enum: ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']),
            new OA\Property(property: 'payment_status', type: 'string', example: 'pending', enum: ['pending', 'paid', 'failed', 'refunded']),
            new OA\Property(property: 'payment_method', type: 'string', example: 'cash_on_delivery', enum: ['cash_on_delivery', 'bank_transfer', 'credit_card']),
            new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 159.98),
            new OA\Property(property: 'shipping_fee', type: 'number', format: 'float', example: 5.00),
            new OA\Property(property: 'total', type: 'number', format: 'float', example: 164.98),
            new OA\Property(property: 'shipping_address', type: 'string', example: '123 Main St, City'),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'items_count', type: 'integer', example: 3),
        ],
    ),
]
class OrderSchema {}

#[
    OA\Schema(
        schema: 'OrderDetail',
        allOf: [
            new OA\Schema(ref: '#/components/schemas/Order'),
            new OA\Schema(type: 'object', properties: [
                new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/OrderItem')),
            ]),
        ],
    ),
]
class OrderDetailSchema {}

#[
    OA\Schema(
        schema: 'Review',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'rating', type: 'integer', example: 5, minimum: 1, maximum: 5),
            new OA\Property(property: 'comment', type: 'string', nullable: true, example: 'Great product!'),
            new OA\Property(property: 'user_name', type: 'string', example: 'John Doe'),
            new OA\Property(property: 'created_at', type: 'string', format: 'date', example: '2026-06-23'),
        ],
    ),
]
class ReviewSchema {}

#[
    OA\Schema(
        schema: 'AuthTokenResponse',
        type: 'object',
        properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Login successful.'),
            new OA\Property(property: 'user', ref: '#/components/schemas/User'),
            new OA\Property(property: 'token', type: 'string', example: '1|abc123def456...'),
        ],
    ),
]
class AuthTokenResponseSchema {}

#[
    OA\Schema(
        schema: 'MessageResponse',
        type: 'object',
        properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Action completed successfully.'),
        ],
    ),
]
class MessageResponseSchema {}

#[
    OA\Schema(
        schema: 'ErrorResponse',
        type: 'object',
        properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Error message here.'),
        ],
    ),
]
class ErrorResponseSchema {}

#[
    OA\Schema(
        schema: 'PaginationMeta',
        type: 'object',
        properties: [
            new OA\Property(property: 'current_page', type: 'integer', example: 1),
            new OA\Property(property: 'last_page', type: 'integer', example: 5),
            new OA\Property(property: 'per_page', type: 'integer', example: 16),
            new OA\Property(property: 'total', type: 'integer', example: 80),
        ],
    ),
]
class PaginationMetaSchema {}
