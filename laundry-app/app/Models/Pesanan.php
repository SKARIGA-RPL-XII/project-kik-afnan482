<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

   protected $fillable = [
    'invoice',
    'user_id',
    'customer_name',
    'customer_phone',
    'service_type',
    'weight',
    'is_express',
    'price_per_kg',
    'express_fee',
    'delivery_fee',
    'subtotal',
    'total',
    'address',
    'notes',
    'payment_method',
    'status',
    'estimated_duration',
];


    protected $casts = [
        'weight' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'express_fee' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'is_express' => 'boolean',
    ];

    /**
     * Generate invoice number
     */
    public static function generateInvoice()
    {
        $year = date('Y');
        $lastOrder = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastOrder ? intval(substr($lastOrder->invoice, -3)) + 1 : 1;
        
        return 'LND-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Relationship dengan User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get service name
     */
    public function getServiceNameAttribute()
    {
        $services = [
            'cuci-kering' => 'Cuci Kering',
            'cuci-setrika' => 'Cuci Setrika',
            'setrika-saja' => 'Setrika Saja',
        ];

        return $services[$this->service_type] ?? $this->service_type;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'proses' => 'blue',
            'selesai' => 'green',
            'diambil' => 'gray',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    public function layanan()
{
    return $this->belongsTo(Layanan::class);
}
}