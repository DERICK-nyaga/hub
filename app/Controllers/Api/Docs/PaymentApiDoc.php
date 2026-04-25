<?php

namespace App\Controllers\Api\Docs;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="Payment & Bill Management API"
 * )
 *
 * @OA\Schema(
 *     schema="Payment",
 *     required={"title", "station_id", "amount", "due_date", "status", "type"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Monthly Electricity Bill"),
 *     @OA\Property(property="station_id", type="integer", example=1),
 *     @OA\Property(property="vendor_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="amount", type="number", format="float", example=1500.00),
 *     @OA\Property(property="due_date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="status", type="string", enum={"pending", "paid", "cancelled"}, example="pending"),
 *     @OA\Property(property="type", type="string", enum={"bill", "payment", "subscription", "other"}, example="bill"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Q4 2023 electricity consumption"),
 *     @OA\Property(property="reference_number", type="string", maxLength=100, nullable=true, example="INV-2023-001"),
 *     @OA\Property(property="payment_method", type="string", maxLength=50, nullable=true, example="Bank Transfer"),
 *     @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="PaymentResource",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Payment"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="station",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="code", type="string")
 *             )
 *         ),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="vendor",
 *                 type="object",
 *                 nullable=true,
 *                 @OA\Property(property="id", type="integer"),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="contact_person", type="string"),
 *                 @OA\Property(property="phone", type="string"),
 *                 @OA\Property(property="email", type="string")
 *             )
 *         ),
 *         @OA\Schema(
 *             @OA\Property(property="formatted_amount", type="string", example="1,500.00"),
 *             @OA\Property(property="formatted_due_date", type="string", example="Jan 15, 2024"),
 *             @OA\Property(property="is_overdue", type="boolean"),
 *             @OA\Property(property="links", type="object")
 *         )
 *     )
 * )
 */
class PaymentApiDoc
{
    // This class is only for documentation purposes
}
