<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PopupLead extends Model
{
    use HasFactory;

    protected $table = 'popup_leads';

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'notes',
        'source_url',
        'ip_address',
        'user_agent',
        'status',
        'admin_notes',
        'contacted_at',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];

    /**
     * Scope for pending leads
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for contacted leads
     */
    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    /**
     * Scope for converted leads
     */
    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    /**
     * Scope for today's leads
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * Badge class helper based on status
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending'   => ['class' => 'bg-warning-subtle text-warning border border-warning-subtle', 'label' => 'Pending / New'],
            'contacted' => ['class' => 'bg-info-subtle text-info border border-info-subtle', 'label' => 'Contacted'],
            'interested'=> ['class' => 'bg-primary-subtle text-primary border border-primary-subtle', 'label' => 'Interested'],
            'converted' => ['class' => 'bg-success-subtle text-success border border-success-subtle', 'label' => 'Converted / Won'],
            'closed'    => ['class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle', 'label' => 'Closed / Not Interested'],
            default     => ['class' => 'bg-light text-muted border', 'label' => ucfirst($this->status)],
        };
    }
}
