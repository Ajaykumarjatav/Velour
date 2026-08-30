<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AppointmentService extends Model
{
    protected $fillable = ['appointment_id','service_id','service_name','duration_minutes','price','sort_order','line_meta'];

    protected $casts = [
        'price'     => 'decimal:2',
        'line_meta' => 'array',
    ];
    public function appointment() { return $this->belongsTo(Appointment::class); }
    public function service()     { return $this->belongsTo(Service::class); }
}
