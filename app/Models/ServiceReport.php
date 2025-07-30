<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReport extends Model
{
    protected $fillable = [
        'service_report_number',
        'client_id',
        'service_date',
        'ac_work',
        'plumbing_work',
        'paint_work',
        'electrical_work',
        'civil_work',
        'job_details',
        'before_pictures',
        'after_pictures',
    ];

    protected $casts = [
        'service_date' => 'date',
        'ac_work' => 'boolean',
        'plumbing_work' => 'boolean',
        'paint_work' => 'boolean',
        'electrical_work' => 'boolean',
        'civil_work' => 'boolean',
        'before_pictures' => 'array',
        'after_pictures' => 'array',
    ];

    /**
     * Get the client that owns the service report.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Generate a unique service report number.
     */
    public static function generateServiceReportNumber(): string
    {
        $prefix = 'SR';
        $year = date('Y');
        $month = date('m');
        
        $lastReport = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastReport ? (int) substr($lastReport->service_report_number, -4) + 1 : 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
